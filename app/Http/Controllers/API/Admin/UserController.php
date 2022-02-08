<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;

class UserController extends Controller
{
    public $model;
    public $type = null;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $items = $this->model->whereProfileableType($this->type)->latest()->get();
        return successResponse(UserResource::collection($items));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $item = $this->model->whereProfileableType($this->type)->findorFail($id);
        $item->delete();
        return successResponse(new UserResource($item));
    }
}
