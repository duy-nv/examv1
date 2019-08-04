<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\User\UpdateManagerRequest;
use App\Http\Resources\User\UserResource;
use App\Models\Manager;
use App\Repositories\User\ManagerRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ManagerController extends Controller
{
  public $managerRepository;

  /**
   * PHP 5 allows developers to declare constructor methods for classes.
   * Classes which have a constructor method call this method on each newly-created object,
   * so it is suitable for any initialization that the object may need before it is used.
   *
   * Note: Parent constructors are not called implicitly if the child class defines a constructor.
   * In order to run a parent constructor, a call to parent::__construct() within the child constructor is required.
   *
   * param [ mixed $args [, $... ]]
   * @link https://php.net/manual/en/language.oop5.decon.php
   */
  public function __construct(
    ManagerRepository $managerRepository
  ){
    $this->managerRepository = $managerRepository;
  }

  /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
      $conditions = [
        'orderBy' => ($request->orderBy ? $request->orderBy : 'username'),
        'order' => ($request->order && in_array($request->order, ['desc', 'asc']) ? $request->order : 'asc'),
        'limit' => ($request->limit && intval($request->limit) > 0 ? $request->limit : 10),
        'search' => ($request->search ? $request->search : ''),
        'roles' => ($request->roles ? $request->roles : ''),
      ];
      $managers = null;
      $keyword = $request->keyword;
      if (!$conditions['roles']) {
        $managers = $this->managerRepository
          ->with(['roles', 'permissions'])
//          ->where('username', "%{$keyword}%", 'like')
//          ->orWhere('first_name', 'like', "%{$keyword}%")
//          ->orWhere('last_name', 'like', "%{$keyword}%")
//          ->orWhere('code', 'like', "%{$keyword}%")
//          ->orWhere('email', 'like', "%{$keyword}%")
          ->notRole(config('access.roles_list.admin'), 'manager')
          ->orderBy($conditions['orderBy'], $conditions['order'])
          ->paginate($conditions['limit']);
      } else {
        $managers = $this->managerRepository
          ->with(['roles', 'permissions'])
//          ->where('username', "%{$keyword}%", 'like')
//          ->orWhere('first_name', 'like', "%{$keyword}%")
//          ->orWhere('last_name', 'like', "%{$keyword}%")
//          ->orWhere('code', 'like', "%{$keyword}%")
//          ->orWhere('email', 'like', "%{$keyword}%")
          ->role($conditions['roles'], 'manager')
          ->notRole(config('access.roles_list.admin'), 'manager')
          ->orderBy($conditions['orderBy'], $conditions['order'])
          ->paginate($conditions['limit']);
      }
      return UserResource::collection($managers);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      $data = $request->all();
      return $this->managerRepository->create($data);
    }

  public function active(Request $request)
  {
    $uuid = $request->uuid;
    $user = $this->managerRepository->updateByUuid($uuid, ['is_actived' => Manager::ACTIVED_CODE]);
    return $user;
  }

  public function deactive(Request $request)
  {
    $uuid = $request->uuid;
    $user = $this->managerRepository->updateByUuid($uuid, ['is_actived' => Manager::DEACTIVED_CODE]);
    return $user;
  }

  /**
   * Display the specified resource.
   *
   * @param \App\Model\Chapter $chapter
   * @return \Illuminate\Http\Response
   */
  public function show(Manager $user)
  {
    return new UserResource($user);
  }

  /**
   * Update the specified resource in storage.
   *
   */
  public function update(UpdateManagerRequest $request, Manager $manager)
  {
    $updatedData = $request->only([
      'username', 'code', 'first_name', 'last_name', 'email', 'role_ids'
    ]);
    return $this->managerRepository->update($manager, $updatedData);
  }

  /**
   * Remove the specified resource from storage.
   *
   * @return \Illuminate\Http\Response
   */
  public function destroy(Manager $user)
  {
    $this->managerRepository->deleteByUuid($user->uuid);
  }
}