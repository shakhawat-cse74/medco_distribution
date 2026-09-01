<div id="addWarehouse" tabindex="-1" role="dialog" aria-labelledby="addWarehouseLabel" aria-hidden="true" class="modal fade text-left">
  <div role="document" class="modal-dialog modal-sm">
    <div class="modal-content">
        <form action="{{ route('warehouse.store') }}" method="POST" id="warehouse-form">
            @csrf
      <div class="modal-header bg-light py-2">
        <h5 id="addWarehouseLabel" class="modal-title"><i class="ti ti-building-warehouse mr-1"></i> {{__('db.Add Warehouse')}}</h5>
        <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="ti ti-x"></i></span></button>
      </div>
      <div class="modal-body p-3">
          <div class="form-group mb-2">
            <label class="small mb-1">{{__('db.name')}} <span class="text-danger">*</span></label>
            <input type="text" placeholder="{{ __('db.Type WareHouse Name') }}" name="name" required="required" class="form-control form-control-sm">
          </div>
          <div class="form-group mb-2">
            <label class="small mb-1">{{__('db.Phone Number')}} <span class="text-danger">*</span></label>
            <input type="text" name="phone" class="form-control form-control-sm" required>
          </div>
          <div class="form-group mb-2">
            <label class="small mb-1">{{__('db.Email')}}</label>
            <input type="email" name="email" placeholder="example@example.com" class="form-control form-control-sm">
          </div>
          <div class="form-group mb-2">
            <label class="small mb-1">{{__('db.Address')}} <span class="text-danger">*</span></label>
            <textarea required class="form-control form-control-sm" rows="2" name="address"></textarea>
          </div>
          <div class="form-group mb-2">
            <label class="small mb-1">{{__('db.Status')}}</label>
            <select class="form-control form-control-sm" name="is_active">
              <option value="1">Active</option>
              <option value="0">Inactive</option>
            </select>
          </div>
          <div class="text-right">
            <button type="button" data-dismiss="modal" class="btn btn-sm btn-secondary">{{__('db.Cancel')}}</button>
            <button type="submit" value="{{__('db.submit')}}" class="btn btn-sm btn-primary warehouse-submit-btn">{{__('db.Save')}}</button>
          </div>
      </div>
      </form>
    </div>
  </div>
</div>
