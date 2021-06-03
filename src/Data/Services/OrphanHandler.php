<?php

namespace Core\Data\Services;

trait OrphanHandler
{

  protected $repo;

  protected $model;
  /**
   * Orphan the specified custom field from storage.
   * @param int $id
   * @return Response
   */
  public function doOrphan(Request $request, $id)
  {
    $Model = $this->model->withTrashed()->withOrphaned();
    return $this->repo->doOrphan($request, $Model->findOrFail($id));
  }

  /**
   * Restore the orphaned custom field into storage.
   * @param int $id
   * @return Response
   */
  public function deOrphan(Request $request, $id)
  {
    $Model = $this->model->withTrashed()->withOrphaned();
    return $this->repo->doOrphan($request, $Model->findOrFail($id));
  }

}
