<?php

namespace App\Http\Controllers\API\User;

use App\Http\Resources\TournamentRoundMatchesResource;
use App\Models\News;
use App\Models\Tournament;
use App\Models\ClubProfile;
use Illuminate\Http\Request;
use App\Models\PlayerProfile;
use App\Http\Controllers\Controller;
use App\Http\Resources\NewsResource;
use App\Http\Resources\TournamentResource;
use App\Http\Resources\ClubProfileResource;
use App\Http\Resources\PlayerProfileResource;

class DataController extends Controller
{
    private $news;
    private $club;
    private $player;
    private $tournament;

    public function __construct(News $news, ClubProfile $club, PlayerProfile $player, Tournament $tournament)
    {
        $this->news = $news;
        $this->club = $club;
        $this->player = $player;
        $this->tournament = $tournament;
    }

    public function playerSignup()
    {
        $clubs = $this->club->latest()->select('id', 'name')->get();

        return successResponse(ClubProfileResource::collection($clubs));
    }

    public function clubsPage($page, $key=""){
        $clubs = $this->club->where('name', 'LIKE', '%'.$key.'%')->latest()->get()->skip(($page-1)*16)->take(16);

        return successResponse(ClubProfileResource::collection($clubs));
    }

    public function clubs()
    {
        $clubs = $this->club->latest()->get();

        return successResponse(count($clubs));
    }

    public function clubsAll($key)
    {
        $clubs = $this->club->where('name', 'LIKE', '%'.$key.'%')->latest()->get();

        return successResponse(count($clubs));
    }

    public function club($id)
    {
        $club = $this->club->with('members.player')->findorFail($id);

        return successResponse(new ClubProfileResource($club));
    }

    public function playersPage($page, $key=""){
        $players = $this->player->with([
            'firstTournamentRoundMatches' => function ($query) {
                $query->whereNotNull('first_player_score')->whereNotNull('first_player_points')->whereNotNull('second_player_points')->whereNotNull('second_player_score');
            },
            'secondTournamentRoundMatches' => function ($query) {
                $query->whereNotNull('second_player_score')->whereNotNull('second_player_points')->whereNotNull('first_player_points')->whereNotNull('first_player_score');
            },
        ])->latest()->where('first_name', 'LIKE', '%'.$key.'%')->orWhere('last_name', 'LIKE', '%'.$key.'%')->orWhere('nick_name', 'LIKE', '%'.$key.'%')->get();

        foreach ($players as $player) {
            $player->matches = $player->firstTournamentRoundMatches->merge($player->secondTournamentRoundMatches);
            $id = $player->id;

            $first = $player->matches->where('first_player_id', $id)->where('second_player_id', '!=', $id);
            $second = $player->matches->where('second_player_id', $id)->where('first_player_id', '!=', $id);

            $player->played_games = $player->matches->count();

            $firstWins = $first->filter(function ($match) {
                return $match->first_player_score > $match->second_player_score;
            });
            $secondWins = $second->filter(function ($match) {
                return $match->second_player_score > $match->first_player_score;
            });
            $player->win_games = $firstWins->count() + $secondWins->count();

            $firstDraw = $first->filter(function ($match) {
                return $match->first_player_score == $match->second_player_score;
            });
            $secondDraw = $second->filter(function ($match) {
                return $match->second_player_score == $match->first_player_score;
            });
            $player->draw_games = $firstDraw->count() + $secondDraw->count();

            $firstLost = $first->filter(function ($match) {
                return $match->first_player_score < $match->second_player_score;
            });
            $secondLost = $second->filter(function ($match) {
                return $match->second_player_score < $match->first_player_score;
            });
            $player->lost_games = $firstLost->count() + $secondLost->count();

            $firstGoals = $first->sum('first_player_score');
            $secondGoals = $second->sum('second_player_score');
            $player->goals = $firstGoals + $secondGoals;

            $firstPoints = $first->sum('first_player_points');
            $secondPoints = $second->sum('second_player_points');
            $player->points = $firstPoints + $secondPoints;
        }
        $players = $players->sortByDesc('points')->skip(($page-1)*25)->take(25);

        return successResponse(PlayerProfileResource::collection($players));
    }

    public function players()
    {
        $players = $this->player->latest()->get();
        return successResponse(count($players));
    }

    public function playersAll($key)
    {
        $players = $this->player->where('first_name', 'LIKE', '%'.$key.'%')->orWhere('last_name', 'LIKE', '%'.$key.'%')->orWhere('nick_name', 'LIKE', '%'.$key.'%')->latest()->get();
        return successResponse(count($players));
    }

    public function player($id)
    {
        $player = $this->player->select('id', 'first_name', 'last_name', 'nick_name', 'biography', 'avatar')->with([
            'tournaments:id,name',
            'membership.club:id,name,avatar',
            'firstTournamentRoundMatches' => function ($query) use ($id) {
                $query->whereNull('first_player_score')->whereNull('first_player_points')->whereNull('second_player_points')->whereNull('second_player_score')->whereNotNull('held_date')->with(['firstPlayer:id,nick_name,avatar', 'secondPlayer:id,nick_name,avatar']);
            },
            'secondTournamentRoundMatches' => function ($query) use ($id) {
                $query->whereNull('second_player_score')->whereNull('second_player_points')->whereNull('first_player_points')->whereNull('first_player_score')->whereNotNull('held_date')->with(['firstPlayer:id,nick_name,avatar', 'secondPlayer:id,nick_name,avatar', 'tournamentRound:id,tournament_id', 'tournamentRound.tournament:id,name']);
            },
            'tournaments.matches' => function ($query) use ($id) {
                $query->whereNotNull('first_player_score')->whereNotNull('first_player_points')->whereNotNull('second_player_points')->whereNotNull('second_player_score')
                    ->where(function ($query) use ($id) {
                        $query->where(function ($query) use ($id) {
                            $query->where('first_player_id', $id)
                                ->where('second_player_id', '!=', $id);
                        })->orWhere(function ($query) use ($id) {
                            $query->where('second_player_id', $id)
                                ->where('first_player_id', '!=', $id);
                        });
                    });
            },
        ])->findorFail($id);

        $player = $this->getPlayerStates($player);

        return successResponse(new PlayerProfileResource($player));
    }

    public function getPlayerStates($player)
    {
        $id = $player->id;
        $player->played_games = 0;
        $player->win_games = 0;
        $player->lost_games = 0;
        $player->draw_games = 0;
        $player->goals = 0;
        $player->points = 0;

        foreach ($player->tournaments as $tournament) {
            $first = $tournament->matches->where('first_player_id', $id)->where('second_player_id', '!=', $id);
            $second = $tournament->matches->where('second_player_id', $id)->where('first_player_id', '!=', $id);

            $tournament->played_games = $tournament->matches->count();

            $firstWins = $first->filter(function ($match) {
                return $match->first_player_score > $match->second_player_score;
            });
            $secondWins = $second->filter(function ($match) {
                return $match->second_player_score > $match->first_player_score;
            });
            $tournament->win_games = $firstWins->count() + $secondWins->count();

            $firstDraw = $first->filter(function ($match) {
                return $match->first_player_score == $match->second_player_score;
            });
            $secondDraw = $second->filter(function ($match) {
                return $match->second_player_score == $match->first_player_score;
            });
            $tournament->draw_games = $firstDraw->count() + $secondDraw->count();

            $firstLost = $first->filter(function ($match) {
                return $match->first_player_score < $match->second_player_score;
            });
            $secondLost = $second->filter(function ($match) {
                return $match->second_player_score < $match->first_player_score;
            });
            $tournament->lost_games = $firstLost->count() + $secondLost->count();

            $firstGoals = $first->sum('first_player_score');
            $secondGoals = $second->sum('second_player_score');
            $tournament->goals = $firstGoals + $secondGoals;

            $firstPoints = $first->sum('first_player_points');
            $secondPoints = $second->sum('second_player_points');
            $tournament->points = $firstPoints + $secondPoints;

            $player->played_games += $tournament->played_games;
            $player->win_games += $tournament->win_games;
            $player->lost_games += $tournament->lost_games;
            $player->draw_games += $tournament->draw_games;
            $player->goals += $tournament->goals;
            $player->points += $tournament->points;
        }

        $player->matches = $player->matches->sortBy('held_date');

        return $player;
    }

    public function tournaments(Request $request)
    {
        $tournaments = $this->tournament->latest()->withCount('participants')->get();

        $tournaments = TournamentResource::collection($tournaments);
        if (auth()->guard('user')->check()) {
            $registered = $this->tournament->whereHas('participants', function ($query) {
                $user = auth()->guard('user')->user();
                $query->where('id', $user->profileable->id);
            })->select('id')->get();
            $registered = $registered->modelKeys();
            $tournaments->with = [
                'registered' => $registered
            ];
        }

        return successResponse($tournaments);
    }

    public function tournament($id)
    {
        $tournament = $this->tournament->withCount('participants')->with([
            'rounds.tournamentRoundMatches.firstPlayer',
            'rounds.tournamentRoundMatches.firstPlayer.membership' => function ($query) {
                $query->with('club:id,name')->whereStatus(1);
            },
            'rounds.tournamentRoundMatches.secondPlayer',
            'rounds.tournamentRoundMatches.secondPlayer.membership' => function ($query) {
                $query->with('club:id,name')->whereStatus(1);
            },
        ])->findorFail($id);

        return successResponse(new TournamentResource($tournament));
    }

    public function latestfourgames()
    {
        $columns = ['first_player_id', 'second_player_id', 'held_date', 'held_time', 'first_player_score', 'second_player_score', 'first_player_points', 'second_player_points'];
        $games = $this->tournament->matches()->getRelated()->limit(4)->with(['firstPlayer', 'secondPlayer', 'tournamentRound.tournament']);
        foreach ($columns as $column) {
            $games = $games->whereNotNull($column);
        }
        $games = $games->get();
        return successResponse(TournamentRoundMatchesResource::collection($games));
    }

    public function bestplayers() {
        return $this->bestfiveplayers(25);
    }

    public function bestfiveplayers($limit = 5)
    {
        $players = $this->player

            // Points
            ->withCount([
                'firstTournamentRoundMatches as first_points' =>
                    function ($query) {
                        $query->whereYear('held_date', now()->year)->select(\DB::raw("COALESCE(SUM(tournament_round_matches.first_player_points),0)"));
                    },
                'secondTournamentRoundMatches as second_points' =>
                    function ($query) {
                        $query->whereYear('held_date', now()->year)->select(\DB::raw("COALESCE(SUM(tournament_round_matches.second_player_points),0)"));
                    }
            ])
            ->selectRaw("(select first_points + second_points) as points")

            // Score
            ->withCount([
                'firstTournamentRoundMatches as first_score' =>
                    function ($query) {
                        $query->whereYear('held_date', now()->year)->select(\DB::raw("COALESCE(SUM(tournament_round_matches.first_player_score),0)"));
                    },
                'secondTournamentRoundMatches as second_score' =>
                    function ($query) {
                        $query->whereYear('held_date', now()->year)->select(\DB::raw("COALESCE(SUM(tournament_round_matches.second_player_score),0)"));
                    }
            ])
            ->selectRaw("(select first_score + second_score) as goals")

            // Wins
            ->withCount([
                'firstTournamentRoundMatches as first_wins' =>
                    function ($query) {
                        $query->whereYear('held_date', now()->year)->whereNotNull('first_player_score')->whereNotNull('second_player_score')->select(\DB::raw("COALESCE(SUM(tournament_round_matches.first_player_score > tournament_round_matches.second_player_score), 0)"));
                    },
                'secondTournamentRoundMatches as second_wins' =>
                    function ($query) {
                        $query->whereYear('held_date', now()->year)->whereNotNull('second_player_score')->whereNotNull('second_player_score')->select(\DB::raw("COALESCE(SUM(tournament_round_matches.second_player_score > tournament_round_matches.first_player_score), 0)"));
                    }
            ])
            ->selectRaw("(select first_wins + second_wins) as win_games")

            // Lost
            ->withCount([
                'firstTournamentRoundMatches as first_lost' =>
                    function ($query) {
                        $query->whereYear('held_date', now()->year)->whereNotNull('first_player_score')->whereNotNull('second_player_score')->select(\DB::raw("COALESCE(SUM(tournament_round_matches.first_player_score < tournament_round_matches.second_player_score), 0)"));
                    },
                'secondTournamentRoundMatches as second_lost' =>
                    function ($query) {
                        $query->whereYear('held_date', now()->year)->whereNotNull('second_player_score')->whereNotNull('second_player_score')->select(\DB::raw("COALESCE(SUM(tournament_round_matches.second_player_score < tournament_round_matches.first_player_score), 0)"));
                    }
            ])
            ->selectRaw("(select first_lost + second_lost) as lost_games")

            ->orderBy('points', 'desc')->limit($limit)->get();
        return successResponse(PlayerProfileResource::collection($players));
    }

    public function news(Request $request)
    {
        $localParams = [
            'perPage' => 10,
        ];
        $params = array_merge($localParams, $request->query());

        $news = $this->news->latest()->select('id', 'title', 'slug', 'description', 'image', 'created_at')->paginate($params['perPage']);

        return successResponse(NewsResource::collection($news));
    }

    public function newsSingle($slug)
    {
        $news = $this->news->whereSlug($slug)->firstorFail();

        return successResponse(new NewsResource($news));
    }
}
