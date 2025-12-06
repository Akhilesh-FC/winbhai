@extends('admin.body.adminmaster')
@section('admin')

<div class="container-fluid">

{{-- ===================== CATEGORIES ===================== --}}
<div class="card mb-4">
  <div class="card-header d-flex justify-content-between">
    <h4>Categories</h4>
    <button class="btn btn-primary"
            data-bs-toggle="modal"
            data-bs-target="#addCatModal">
      + Add Category
    </button>
  </div>

  <div class="card-body">
    <table class="table table-bordered">
      @foreach($categories as $c)
      <tr>
        <td>{{ $c->name }}</td>
        <td width="120">
          <button class="btn btn-warning btn-sm"
                  data-bs-toggle="modal"
                  data-bs-target="#editCat{{ $c->id }}">
            Edit
          </button>
        </td>
      </tr>

      {{-- Edit Category Modal --}}
      <div class="modal fade" id="editCat{{ $c->id }}" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
          <form method="POST" action="{{ route('admin.category.update',$c->id) }}" class="modal-content">
            @csrf
            <div class="modal-header">
              <h5>Edit Category</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <input class="form-control" name="name" value="{{ $c->name }}">
            </div>
            <div class="modal-footer">
              <button class="btn btn-primary">Update</button>
            </div>
          </form>
        </div>
      </div>
      @endforeach
    </table>
  </div>
</div>

{{-- Add Category Modal --}}
<div class="modal fade" id="addCatModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <form method="POST" action="{{ route('admin.category.store') }}" class="modal-content">
      @csrf
      <div class="modal-header">
        <h5>Add Category</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input class="form-control" name="name" placeholder="Category Name">
      </div>
      <div class="modal-footer">
        <button class="btn btn-success">Save</button>
      </div>
    </form>
  </div>
</div>

{{-- ===================== LANGUAGES ===================== --}}
<div class="card mb-4">
  <div class="card-header d-flex justify-content-between">
    <h4>Languages</h4>
    <button class="btn btn-primary"
            data-bs-toggle="modal"
            data-bs-target="#addLangModal">
      + Add Language
    </button>
  </div>

  <div class="card-body">
    <table class="table table-bordered">
      @foreach($languages as $l)
      <tr>
        <td>{{ $l->name }}</td>
        <td width="120">
          <button class="btn btn-warning btn-sm"
                  data-bs-toggle="modal"
                  data-bs-target="#editLang{{ $l->id }}">
            Edit
          </button>
        </td>
      </tr>

      {{-- Edit Language Modal --}}
      <div class="modal fade" id="editLang{{ $l->id }}" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
          <form method="POST" action="{{ route('admin.language.update',$l->id) }}" class="modal-content">
            @csrf
            <div class="modal-header">
              <h5>Edit Language</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <input class="form-control" name="name" value="{{ $l->name }}">
            </div>
            <div class="modal-footer">
              <button class="btn btn-primary">Update</button>
            </div>
          </form>
        </div>
      </div>
      @endforeach
    </table>
  </div>
</div>

{{-- Add Language Modal --}}
<div class="modal fade" id="addLangModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <form method="POST" action="{{ route('admin.language.store') }}" class="modal-content">
      @csrf
      <div class="modal-header">
        <h5>Add Language</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input class="form-control" name="name" placeholder="Language Name">
      </div>
      <div class="modal-footer">
        <button class="btn btn-success">Save</button>
      </div>
    </form>
  </div>
</div>

{{-- ===================== MEDIA ===================== --}}
<div class="card">
  <div class="card-header">
    <h4>Add Category Language Media</h4>
  </div>

  <div class="card-body">
    <form method="POST" action="{{ route('admin.media.store') }}" class="row g-2 mb-3">
      @csrf
      <div class="col-md-3">
        <select name="category_id" class="form-control">
          @foreach($categories as $c)
            <option value="{{ $c->id }}">{{ $c->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-3">
        <select name="language_id" class="form-control">
          @foreach($languages as $l)
            <option value="{{ $l->id }}">{{ $l->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-3">
        <input name="image_url" class="form-control" placeholder="Image URL">
      </div>
      <div class="col-md-3">
        <input name="video_url" class="form-control" placeholder="Video URL">
      </div>
      <div class="col-md-12">
        <button class="btn btn-success mt-2">Add Media</button>
      </div>
    </form>

    <table class="table table-striped">
      @foreach($media as $m)
      <tr>
        <td>{{ $m->category }}</td>
        <td>{{ $m->language }}</td>
        <td><img src="{{ $m->image_url }}" width="60"></td>
        <td>{{ $m->video_url }}</td>
        <td width="220">
          <form method="POST" action="{{ route('admin.media.update',$m->id) }}" class="d-inline">
            @csrf
            <input name="image_url" value="{{ $m->image_url }}" class="form-control mb-1">
            <input name="video_url" value="{{ $m->video_url }}" class="form-control mb-1">
            <button class="btn btn-primary btn-sm">Update</button>
          </form>

          <form method="POST" action="{{ route('admin.media.delete',$m->id) }}" class="d-inline">
            @csrf
            <button class="btn btn-danger btn-sm mt-1">Delete</button>
          </form>
        </td>
      </tr>
      @endforeach
    </table>

  </div>
</div>

</div>
@endsection
