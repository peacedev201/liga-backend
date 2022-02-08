<?php

namespace App\Http\Controllers\API\Admin;

use App\Models\User;
use App\Http\Resources\UserResource;

class ClubController extends UserController
{
    public function __construct(User $model)
    {
        $this->model = $model;
        $this->type = 'club';
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $item = $this->model->whereProfileableType($this->type)->with('profileable.members.player')->findorFail($id);
        return successResponse(new UserResource($item));
    }
}
