@extends('admin.body.adminmaster')

@section('admin')

<div class="container-fluid">
  <div class="row">
    <div class="col-md-12">
      <div class="white_shd full margin_bottom_30">
        <div class="full graph_head d-flex justify-content-between align-items-center">
          <h2 class="mb-0">Agent List</h2>
          <button type="button" class="btn btn-info" data-toggle="modal" data-target="#addAgentModal">
            + Add Agent
          </button>
        </div>

        <div class="table_section padding_infor_info">
          <div class="table-responsive-sm">
            <table id="example" class="table table-striped" style="width:100%">
              <thead class="thead-dark">
                <tr>
                  <th>Sr.No</th>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Commission</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @foreach($agent as $key => $item)
                <tr>
                  <td>{{ $key+1 }}</td>
                  <td>{{ $item->name }}</td>
                  <td>{{ $item->email }}</td>
                  <td>{{ $item->commission }}%</td>
                  <td>
                    <button class="btn btn-sm {{ $item->status == 1 ? 'btn-success' : 'btn-danger' }}" 
                            id="statusBtn{{ $item->id }}">
                      {{ $item->status == 1 ? 'Active' : 'Inactive' }}
                    </button>
                  </td>
                  <td>
                    <a href="" class="btn btn-primary btn-sm">View</a>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<!-- Add Agent Modal -->
<div class="modal fade" id="addAgentModal" tabindex="-1" role="dialog" aria-labelledby="addAgentModalTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add New Agent</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span>&times;</span>
        </button>
      </div>

      <form action="" method="POST">
        @csrf
        <div class="modal-body">
          <div class="form-group">
            <label for="agentName">Agent Name</label>
            <input type="text" class="form-control" name="name" id="agentName" placeholder="Enter name" required>
          </div>

          <div class="form-group">
            <label for="agentEmail">Email</label>
            <input type="email" class="form-control" name="email" id="agentEmail" placeholder="Enter email" required>
          </div>

          <div class="form-group">
            <label for="agentCommission">Commission (%)</label>
            <input type="number" class="form-control" name="commission" id="agentCommission" placeholder="Enter commission" required>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Save Agent</button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection
