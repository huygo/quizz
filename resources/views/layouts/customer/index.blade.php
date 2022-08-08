@extends('layouts.master')
@section('content')
@section('title', 'Danh sách khách hàng')
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Khách hàng</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="{{URL::to('/')}}">Home</a></li>
              <li class="breadcrumb-item active">Khách hàng</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <div class="card card-info collapsed-card">
          <div class="card-header">
            <h3 class="card-title">Tìm kiếm khách hàng</h3>

            <div class="card-tools">
              <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-plus"></i>
              </button>
              <button type="button" class="btn btn-tool" data-card-widget="remove">
                <i class="fas fa-times"></i>
              </button>
            </div>
          </div>
          <!-- /.card-header -->
          <div class="card-body" style="display: none;">
            <form id="search-kh">
            {!! csrf_field() !!}
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Họ tên:</label>
                  <input type="text" class="form-control my-colorpicker1 colorpicker-element" id="name" data-colorpicker-id="1" data-original-title="" title="Họ tên">
                </div>
                <!-- /.form-group -->
              </div>
              <!-- /.col -->
              <div class="col-md-6">
                <div class="form-group">
                  <label>Điện thoại:</label>
                  <input type="text" class="form-control my-colorpicker1 colorpicker-element" id="phone" data-colorpicker-id="1" data-original-title="" title="">
                </div>
              </div>
              <!-- /.col -->
            </div>
            </form>
            <center>
            <button type="button" class="btn bg-gradient-info btn-lg" onclick="search()"><i class="fas fa-search"></i> Tìm kiếm</button>
            </center>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12"> 
            <div id="content">

            </div>
          </div>
        
        </div>
        
      </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
  </div>

    

  <script type="text/javascript">
	search();
	// search
	function search(){
		var csrfToken = $('input[name="_token"]').val();
    var name = ($('#name').val() != '') ? $('#name').val() : null;
    var phone = ($('#phone').val() != '') ? $('#phone').val() : null;
		var record = ($('#select_record').children("option:selected").val()) ? $('#select_record').children("option:selected").val(): 25;
		var url = "{{ route('customer.search') }}";
		var getData = {
			page: 1,
			record: record,
      name:name,
      phone:phone
		}
    callAjax(url, getData, 'text', 'post', csrfToken, true).then(response=>{
        $('#content').html(response);
    })
	}
	
</script>
  <!-- /.content-wrapper -->
  @endsection