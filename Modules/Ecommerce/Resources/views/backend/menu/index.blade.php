@extends('backend.layout.main') @section('content')
@if(session()->has('not_permitted'))
  <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}</div> 
@endif
<section>
    <div class="container-fluid"> 
        <div class="row">
            <div class="col-md-12">

                @if($errors->any())
                <div class="alert alert-danger alert-dismissible text-center mar-bot-30"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <ul>
                        @foreach($errors->all() as $error)
                        <li>{{ $error}}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                @if(session()->has('message'))
                  <div class="alert alert-{{session('type')}} alert-dismissible text-center mar-bot-30"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session('message') }}</div> 
                @endif
                <button class="btn btn-primary" data-toggle="collapse" data-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">{{ __('db.Add Menu') }}</button>
                <div class="collapse mt-3" id="collapseExample">
                    <div class="card">
                        <div class="card-body">
                            <form action="{{route('menu.store')}}" method="post" class="form-signin" enctype='multipart/form-data'>
                                @csrf
                                <div class="row">
                                    <div class="col-sm-3">
                                        <label>{{ __('db.Title') }}</label><br>
                                        <input class="form-control" type="text" name="title">
                                    </div>
                                    <div class="col-sm-3">
                                        <label>{{ __('db.Location') }}</label><br>
                                        <select class="form-control" name="location">
                                            <option value="1">Main Navigation</option>
                                            <option value="0" selected>Other</option>
                                        </select>
                                    </div>
                                    <div class="col-sm-1">
                                        <label></label><br>
                                        <button class="btn btn-success btn-block mar-top-30" type="submit">{{ __('Save') }}</button>
                                    </div>                                    
                                </div>                               
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>  

    @if(!empty($menus))
    <div class="table-responsive">
        <table id="menu_table" class="table " style="width: 100%">
            <thead>
                <tr>
                    <th class="not-exported"></th>
                    <th>{{__('db.Title')}}</th>
                    <th>{{__('db.Location')}}</th>
                    <th class="not-exported">{{__('db.action')}}</th>
                </tr>
            </thead>
            <tbody>
            @foreach($menus as $menu)
            
                <tr>
                    <td class="not-exported"></td>
                    <td>{{ $menu->title }}</td>
                    <td>
                        @if($menu->location == 1)
                        Main Navigation
                        @else
                        Other
                        @endif
                    </td>
                    <td class="not-exported">
                        <a href="{{ url('menu/') }}/{{ $menu->id }}" class="btn btn-success btn-sm"><i class="dripicons-preview"></i></a>
                        &nbsp;&nbsp;
                        <a data-id="{{$menu->id}}" data-toggle="modal" data-target="#editModal" class="btn btn-primary btn-sm open-EditDialog" href="#">
                            <i class="dripicons-pencil"></i>
                        </a>&nbsp;&nbsp;
                        <a href="{{ url('menu/delete/') }}/{{ $menu->id }}" onclick="return confirmDelete()" class="btn btn-danger btn-sm"><i class="dripicons-trash"></i></a>
                    </td>
                </tr>
            
            @endforeach
            </tbody>
        </table>
    </div>
    @endif
</section>

    <div id="editModal" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 id="exampleModalLabel" class="modal-title">{{__('Edit Menu')}}</h5>
                    <button type="button" data-dismiss="modal" id="close" aria-label="Close" class="close"><span
                                aria-hidden="true">×</span></button>
                </div>

                <div class="modal-body">
                    <form id="edit-link-form" method="post" action="{{route('menu.update')}}" class="form-horizontal">

                        @csrf
                        <div class="row">

                            <div class="col-sm-3">
                                <label>{{ __('db.Title') }}</label><br>
                                <input id="title" class="form-control" type="text" name="title">
                            </div>
                            <div class="col-sm-3">
                                <label>{{ __('db.Location') }}</label><br>
                                <select class="form-control" name="location" id="location">
                                    <option value="1">Main Navigation</option>
                                    <option value="0">Other</option>
                                </select>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <label></label><br>
                                    <input type="hidden" name="menuid" value=""/>
                                    <input type="submit" name="action_button" id="edit-page" class="btn btn-warning"
                                           value="{{__('db.Save')}}">
                                </div>
                            </div>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script type="text/javascript">
    "use strict";

    function confirmDelete() {
      if (confirm("Are you sure want to delete?")) {
          return true;
      }
      return false;
    }

    $('#menu_table').DataTable( {
        "order": [],
        'language': {
            'lengthMenu': '_MENU_ {{trans("file.records per page")}}',
            "info":      '<small>{{trans("file.Showing")}} _START_ - _END_ (_TOTAL_)</small>',
            "search":  '{{trans("file.Search")}}',
            'paginate': {
                    'previous': '<i class="dripicons-chevron-left"></i>',
                    'next': '<i class="dripicons-chevron-right"></i>'
            }
        },
        'columnDefs': [
            {
                "orderable": false,
                'targets': [0, 1, 2]
            },
            {
                'render': function(data, type, row, meta){
                    if(type === 'display'){
                        data = '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>';
                    }

                return data;
                },
                'checkboxes': {
                'selectRow': true,
                'selectAllRender': '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>'
                },
                'targets': [0]
            }
        ],
        'select': { style: 'multi',  selector: 'td:first-child'},
        'lengthMenu': [[10, 25, 50, -1], [10, 25, 50, "All"]],
        dom: '<"row"lfB>rtip',
        buttons: [
            {
                extend: 'pdf',
                text: '<i title="export to pdf" class="fa fa-file-pdf-o"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible',
                    stripHtml: false
                },
                customize: function(doc) {
                    for (var i = 1; i < doc.content[1].table.body.length; i++) {
                        if (doc.content[1].table.body[i][0].text.indexOf('<img src=') !== -1) {
                            var imagehtml = doc.content[1].table.body[i][0].text;
                            var regex = /<img.*?src=['"](.*?)['"]/;
                            var src = regex.exec(imagehtml)[1];
                            var tempImage = new Image();
                            tempImage.src = src;
                            var canvas = document.createElement("canvas");
                            canvas.width = tempImage.width;
                            canvas.height = tempImage.height;
                            var ctx = canvas.getContext("2d");
                            ctx.drawImage(tempImage, 0, 0);
                            var imagedata = canvas.toDataURL("image/png");
                            delete doc.content[1].table.body[i][0].text;
                            doc.content[1].table.body[i][0].image = imagedata;
                            doc.content[1].table.body[i][0].fit = [30, 30];
                        }
                    }
                },
            },
            {
                extend: 'csv',
                text: '<i title="export to csv" class="fa fa-file-text-o"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible',
                    format: {
                        body: function ( data, row, column, node ) {
                            if (column === 0 && (data.indexOf('<img src=') !== -1)) {
                                var regex = /<img.*?src=['"](.*?)['"]/;
                                data = regex.exec(data)[1];                 
                            }
                            return data;
                        }
                    }
                },
            },
            {
                extend: 'print',
                text: '<i title="print" class="fa fa-print"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible',
                    stripHtml: false
                },
            },
            {
                extend: 'colvis',
                text: '<i title="column visibility" class="fa fa-eye"></i>',
                columns: ':gt(0)'
            },
        ],
    });

    $(document).ready(function() {
        $('.open-EditDialog').on('click', function() {
            var url = "menu/edit/"
            var id = $(this).data('id').toString();
            url = url.concat(id);
            $("input[name='menuid']").val($(this).data('id'));
            $.get(url, function(data) {
                $("#title").val(data['title']);
                $("#location").val(data['location']);  
                $("#location").selectpicker('refresh')              
            });
        });
    });
</script>
@endpush
