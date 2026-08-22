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

                <div class="card">
                    <div class="card-body">
                        <button class="btn btn-primary" data-toggle="collapse" data-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">{{ __('db.Add Slider') }}</button>
                        <div class="collapse" id="collapseExample">
                            <hr>
                            <form action="{{route('slidersCreate')}}" method="post" class="form-signin" enctype='multipart/form-data'>
                            @csrf
                                <div class="row add-area">
                                    <div class="col-sm-4">
                                        <label>{{ __('db.Title') }}</label><br>
                                        <input class="form-control" type="text" name="title[]">
                                    </div>
                                    <div class="col-sm-4">
                                        <label>{{ __('db.Link') }}</label><br>
                                        <input class="form-control" type="text" name="link[]">
                                    </div>
                                    <div class="col-sm-3">
                                        <label>{{ __('db.Order') }}</label><br>
                                        <input class="form-control" type="text" name="order[]" value="0">
                                    </div>
                                    <div class="col-sm-1">
                                        <label>{{ __('Add') }}</label>
                                        <a class="btn btn-success btn-sm add-more"><i class="dripicons-plus"></i></a>
                                    </div>
                                    <div class="col-sm-4 mt-2">
                                        <label>{{ __('db.Image') }} ({{ __('db.Desktop') }})<span class="required">*</span></label><br>
                                        <input class="form-control" type="file" name="image1[]" required>
                                    </div>
                                    <div class="col-sm-4 mt-2">
                                        <label>{{ __('db.Image') }} ({{ __('db.Tab') }})</label><br>
                                        <input class="form-control" type="file" name="image2[]">
                                    </div>
                                    <div class="col-sm-4 mt-2">
                                        <label>{{ __('db.Image') }} ({{ __('db.Mobile') }})</label><br>
                                        <input class="form-control" type="file" name="image3[]">
                                    </div>
                                    
                                    <div class="col-12 mt-4"><hr></div>
                                </div>
                                <button class="btn btn-success btn-block mar-top-30" type="submit">{{ __('Save') }}</button>
                            </form>
                        </div>
                    </div>
                </div>

                @if(!empty($sliders))
                <div class="table-responsive">
                    <table id="product-data-table" class="table" style="width: 100%">
                        <thead>
                            <tr>
                                <th class="not-exported"></th>
                                <th>{{__('db.Image')}}</th>
                                <th>{{__('db.Title')}}</th>
                                <th>{{__('db.Link')}}</th>
                                <th class="not-exported">{{__('db.action')}}</th>
                            </tr>
                            @foreach($sliders as $slider)
                            <tr>
                                <td class="not-exported"></td>
                                <td><img style="width: 150px" src="{{ url('frontend/images/slider/desktop') }}/{{ $slider->image1 }}"></td>
                                <td>{{ $slider->title }}</td>
                                <td>{{ $slider->link }}</td>
                                <td class="not-exported"><a href="{{ url('sliders/delete/') }}/{{ $slider->id }}" onclick="return confirmDelete()" class="btn btn-link"><i class="dripicons-trash"></i></a></td>
                            </tr>
                            @endforeach
                        </thead>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>  
</section>
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
    
    $(document).on('click', '.add-more', function(){
        $('.add-area').append('<div class="col-sm-4"><label>{{ __('db.Title') }}</label><br><input class="form-control" type="text" name="title[]"></div><div class="col-sm-4"><label>{{ __('db.Link') }}</label><br><input class="form-control" type="text" name="link[]"></div><div class="col-sm-3"><label>{{ __('db.Order') }}</label><br><input class="form-control" type="text" name="order[]" vlaue="0"></div><div class="col-sm-1"><label>{{ __('Add') }}</label><a class="btn btn-success btn-sm add-more"><i class="dripicons-plus"></i></a></div><div class="col-sm-4 mt-2"><label>{{ __('db.Image') }} ({{ __('db.Desktop') }})<span class="required">*</span></label><br><input class="form-control" type="file" name="image1[]" required></div><div class="col-sm-4 mt-2"><label>{{ __('db.Image') }} ({{ __('db.Tab') }})</label><br><input class="form-control" type="file" name="image2[]"></div><div class="col-sm-4 mt-2"><label>{{ __('db.Image') }} ({{ __('db.Mobile') }})</label><br><input class="form-control" type="file" name="image3[]"></div><div class="col-12 mt-4"><hr></div>');
    })
</script>
@endpush
