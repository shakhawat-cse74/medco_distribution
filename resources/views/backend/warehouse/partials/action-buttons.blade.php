<div class="btn-group">
    <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{__('db.action')}}
        <span class="caret"></span>
        <span class="sr-only">Toggle Dropdown</span>
    </button>
    <ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default" user="menu">
        @can('warehouse-edit')
        <li>
            <button type="button" data-id="{{$warehouse->id}}" class="open-EditWarehouseDialog btn btn-link" data-toggle="modal" data-target="#editModal"><i class="ti ti-edit"></i> {{__('db.edit')}}
        </button>
        </li>
        @endcan
        @can('warehouse-delete')
        <li class="divider"></li>
        <form action="{{ route('warehouse.destroy', $warehouse->id) }}" method="POST">
            @csrf
            @method('DELETE')
            <li>
                <button type="submit" class="btn btn-link" onclick="return confirmDelete()"><i class="ti ti-trash"></i> {{__('db.delete')}}</button>
            </li>
        </form>
        @endcan
    </ul>
</div>