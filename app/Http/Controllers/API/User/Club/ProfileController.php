<?php

namespace App\Http\Controllers\API\User\Club;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Http\Requests\User\Club\ProfileRequest;

class ProfileController extends Controller
{
    private $user;

    public function __construct()
    {
        $this->user = auth()->user();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProfileRequest $request)
    {
        $data = $request->validated();

        $this->user->profileable->update($data);

        $this->user->refresh();
        
        return successResponse(new UserResource($this->user));
    }
}
