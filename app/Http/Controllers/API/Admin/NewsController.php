<?php

namespace App\Http\Controllers\API\Admin;

use App\Models\News;
use App\Http\Controllers\Controller;
use App\Http\Resources\NewsResource;
use App\Http\Requests\Admin\News\StoreRequest;
use App\Http\Requests\Admin\News\UpdateRequest;

class NewsController extends Controller
{
    private $model;

    public function __construct(News $model)
    {
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
        return successResponse(NewsResource::collection($items));
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
        $item = $this->model->create($data);
        return successResponse(new NewsResource($item));
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
        return successResponse(new NewsResource($item));
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
        $item->update($data);
        return successResponse(new NewsResource($item));
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
        return successResponse(new NewsResource($item));
    }
}
