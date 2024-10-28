@extends('backend.layouts.app')

@section('title', app_name() . ' | ' . __('menus.backend.instructors.student_index'))

@section('content')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/dt-1.10.23/datatables.min.css" />

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-sm-5">
                <h4 class="card-title mb-0">
                    {{ __('menus.backend.instructors.student_index') }}
                </h4>
            </div><!-- col-sm-5 -->
        </div><!--row-->

        @include('backend.instructors.includes.seats')

        <div class="row mt-4">
            <div class="col">
                <div class="table-responsive">
                    <table class="table data-table table-striped" id="studentTable" style="width:100%">
                        <thead>
                            <tr>
                                <th class="dt-head-center"></th>
                                <th class="dt-head-center">
                                    <div>@lang('labels.backend.instructors.studentstable.lastname')</div>
                                </th>
                                <th class="dt-head-center">
                                    <div>@lang('labels.backend.instructors.studentstable.firstname')</div>
                                </th>
                                <th class="dt-head-center">
                                    <div>@lang('labels.backend.instructors.studentstable.enabled')</div>
                                </th>
                                <th class="dt-head-center">@lang('labels.general.actions')</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div><!--col-->
        </div><!--row-->
    </div><!--card-body-->
</div><!--card-->

<div class="modal fade" id="instructorStudentModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">@lang('labels.backend.instructors.access_modal.title')</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>
            <div class="modal-body">@lang('labels.backend.instructors.access_modal.delete_message')</div>
            <div class="modal-footer">
                <button type="button" id="btn_removeStudent" class="btn btn-primary" data-dismiss="modal">@lang('labels.backend.instructors.access_modal.button_apply')</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('labels.backend.instructors.access_modal.button_cancel')</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="instructorStudentResultModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">@lang('labels.backend.instructors.access_modal.title')</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>
            <div class="modal-body">@lang('labels.backend.instructors.access_modal.delete_success_message')</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('labels.backend.instructors.access_modal.button_okay')</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('after-scripts')
<script type="text/javascript" src="https://cdn.datatables.net/v/bs4/dt-1.10.23/datatables.min.js"></script>

<script>
    $(document).ready(function () {
        LoadStudentList();
    });

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    $(document.body).on('click', '.btn_allowStudent', function () {
        let studentId = $(this).data('id');
        GiveStudentAccess(studentId);
    });
    $(document.body).on('click', '.btn_denyStudent', function () {
        let studentId = $(this).data('id');
        RemoveStudentAccess(studentId);
    });
    $(document.body).on('click', '.btn_deleteStudent', function () {
        let studentId = $(this).data('id');
        $('#instructorStudentModal').modal('toggle');
        $('#btn_removeStudent').data('id', studentId);
    });

    $(document.body).on('click', '#btn_removeStudent', function () {
        let studentId = $(this).data('id');
        let data = {};
        data['studentId'] = studentId;
        $.ajax({
            type: 'get',
            async: true,
            url: "{{ route('admin.instructors.remove_student_connection') }}",
            data: data,
            success: function (response) {
                if (response.success) {
                    $("#studentTable").DataTable().ajax.reload();
                    $("#instructorStudentResultModal").modal('toggle');
                } else {
                    alert("@lang('labels.backend.instructors.access_modal.fail_message')");
                }
            }
        });
    });

    function LoadStudentList()
    {
        let studentTable = $("#studentTable").DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.instructors.students_list') }}",
            rowId: 'id',
            columns: [
                { data: "id", name: "id", visible: false },
                { data: "last_name", name: "last_name", width: "1em", className: "dt-body-center" },
                { data: "first_name", name: "first_name", width: "1em", className: "dt-body-center" },
                { data: "active", name: "active", width: "1em", className: "dt-body-center" },
                { data: "actions", name: "action", width: "2em", className: "dt-body-center", orderable: false, searchable: false },
            ],
            columnDefs: [{
              "targets": '_all',
              "createdCell": function (cell, cellData, rowData, rowIndex, colIndex) {
                  AddCanAccessId(cell, rowData, colIndex);
               }
            }],
            order: [[1, "asc"]],
            initComplete: function () {
            }
        });
    }

    function AddCanAccessId(cell, rowData, colIndex) {
        let colInfo = $('#studentTable').DataTable().settings()[0].aoColumns[colIndex];
        if (colInfo.sName == "active") {
            $(cell).attr("id", "canAccess_" + rowData.id);
        }
    }

    function GiveStudentAccess(studentId) { ajaxSetAccess(studentId, 1); }
    function RemoveStudentAccess(studentId) { ajaxSetAccess(studentId, 0); }

    function SetCanAccessText(studentId,result) {
        let text = result == 1 ? "@lang('labels.general.yes')" : "@lang('labels.general.no')";
        $("#canAccess_"+studentId).text(text);
    }

    function ajaxSetAccess(studentId, studentCanAccess) {
        let data = {};
        data['studentId'] = studentId;
        data['canAccess'] = studentCanAccess;
        $.ajax({
            type: 'get',
            async: true,
            url: "{{ route('admin.instructors.set_student_course_access') }}",
            data: data,
            success: function (response) {
                if (response.success) {
                    let studentId = response.student_id;
                    let canAccess = response.canAccess;
                    SetCanAccessText(studentId, canAccess);
                } else {
                    alert("@lang('labels.backend.instructors.access_modal.fail_message')");
                }
            }
        });
    }
</script>

@endpush