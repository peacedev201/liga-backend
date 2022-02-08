<?php

namespace App\Http\Controllers\API\Admin;

use App\Models\Admin;
use App\Http\Controllers\Controller;
use App\Http\Resources\AdminResource;
use App\Http\Requests\Admin\Admin\StoreRequest;
use App\Http\Requests\Admin\Admin\UpdateRequest;
use Illuminate\Contracts\Hashing\Hasher as Hash;

class AdminController extends Controller
{
    private $hash;
    private $model;

    public function __construct(Hash $hash, Admin $model)
    {
        $this->hash = $hash;
        $this->model = $model;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $items = $this->model->latest()->get();
        return successResponse(AdminResource::collection($items));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        $data = $request->validated();
        $data['password'] = $this->hash->make($data['password']);
        $item = $this->model->create($data);
        return successResponse(new AdminResource($item));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $item = $this->model->findorFail($id);
        return successResponse(new AdminResource($item));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, $id)
    {
        $data = $request->validated();
        $item = $this->model->findorFail($id);
        if (isset($data['password']) && $data['password']) {
            $data['password'] = $this->hash->make($data['password']);
        }
        $item->update($data);
        return successResponse(new AdminResource($item));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $item = $this->model->findorFail($id);
        $item->delete();
        return successResponse(new AdminResource($item));
    }
}
