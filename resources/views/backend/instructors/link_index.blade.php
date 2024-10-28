@extends('backend.layouts.app')

@section('title', app_name() . ' | ' . __('menus.backend.instructors.links'))

@section('content')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/dt-1.10.23/datatables.min.css" />

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-sm-5">
                <h4 class="card-title mb-0">
                    {{ __('menus.backend.instructors.links') }}
                </h4>
            </div><!-- col-sm-5 -->
        </div><!--row-->

        <div class="row mt-4">
            <div class="col">
                <p>Share a link with students to give them access to all scenarios you create.</p>
                <p><button type="button" class="btn darkblue-button" id="btn_createLink">Create new link</button></p>
                <div class="table-responsive">
                    <table class="table data-table table-striped" id="linkTable" style="width:100%">
                        <thead>
                            <tr>
                                <th class="dt-head-center"></th>
                                <th class="dt-head-center">@lang('labels.backend.instructors.table.name')</th>
                                <th class="dt-head-center">@lang('labels.backend.instructors.table.created_at')</th>
                                <th class="dt-head-center">@lang('labels.backend.instructors.table.status')</th>
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

<div class="modal fade" id="linkAccessModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">@lang('labels.backend.instructors.access_modal.title')</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>
            <div class="modal-body">@lang('labels.backend.instructors.access_modal.delete_link_message')</div>
            <div class="modal-footer">
                <button type="button" id="btn_removeLink" class="btn btn-primary" data-dismiss="modal">@lang('labels.backend.instructors.access_modal.button_apply')</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('labels.backend.instructors.access_modal.button_cancel')</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="linkAccessResultModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">@lang('labels.backend.instructors.access_modal.title')</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>
            <div class="modal-body" id="linkResultBody"></div>
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
    $(document).ready(function() {
        InitLinkTable();
    });

    $(document.body).on('click', '.btn_copyLink', function () {
        let link = $(this).data('link');
        try {
            navigator.clipboard.writeText(link);
        } catch (e) {
            alert("Clipboard requires https");
        }
    });
    
    $(document.body).on('click', '.btn_activateLink', function () {
        let id = $(this).data('id');
        console.log("click btn_activateLink " + id);
        ajaxSetLinkStatus(id, 1);
    });

    $(document.body).on('click', '.btn_deactivateLink', function () {
        let id = $(this).data('id');
        console.log("click btn_deactivateLink " + id);
        ajaxSetLinkStatus(id, 0);
    });

    $(document.body).on('click', '.btn_deleteLink', function () {
        let id = $(this).data('id');
        console.log("click btn_deleteLink " + id);
        $('#linkAccessModal').modal('toggle');
        $('#btn_removeLink').data('id', id);
    });

    $(document.body).on('click', '#btn_createLink', function () {
        $.ajax({
            type: 'get',
            async: true,
            url: "{{ route('admin.instructors.create_course_access_link') }}",
            success: function (response) {
                if (response.success) {
                    $("#linkTable").DataTable().ajax.reload();
                    setResultModalBody("@lang('labels.backend.instructors.access_modal.create_link_success_message')");
                    $("#linkAccessResultModal").modal('toggle');
                } else {
                    alert("@lang('labels.backend.instructors.access_modal.fail_message')");
                }
            }
        });
    });

    $(document.body).on('click', '#btn_removeLink', function () {
        let id = $(this).data('id');
        let data = {};
        data['link_id'] = id;
        $.ajax({
            type: 'get',
            async: true,
            url: "{{ route('admin.instructors.remove_course_access_link') }}",
            data: data,
            success: function (response) {
                if (response.success) {
                    $("#linkTable").DataTable().ajax.reload();
                    setResultModalBody("@lang('labels.backend.instructors.access_modal.delete_link_success_message')");
                    $("#linkAccessResultModal").modal('toggle');
                } else {
                    alert("@lang('labels.backend.instructors.access_modal.fail_message')");
                }
            }
        });
    });

    function InitLinkTable() {
        var table = $("#linkTable").DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.instructors.course_links') }}",
            },
            rowId: 'id',
            columns: [
                { data: "id", name: "id", visible: false },
                { data: "uuid", name: "uuid", width: "1em", className: "dt-body-center", orderable: false },
                { data: "created_at", name: "created_at", width: "1em", className: "dt-body-center" },
                { data: "active", name: "active", width: "1em", className: "dt-body-center", orderable: false },
                { data: "actions", name: "action", width: "2em", className: "dt-body-center", orderable: false, searchable: false },
            ],
            sDom: 'lrtip',
            columnDefs: [{
              "targets": '_all',
              "createdCell": function (cell, cellData, rowData, rowIndex, colIndex) {
                  AddIsActiveId(cell, rowData, colIndex);
               }
            }],
            "order": [[ 0, "desc" ]],
            initComplete: function () {},
            
        });
    }

    function AddIsActiveId(cell, rowData, colIndex) {
        let colInfo = $('#linkTable').DataTable().settings()[0].aoColumns[colIndex];
        if (colInfo.sName == "active") {
            $(cell).attr("id", "isActive_" + rowData.id);
        }
    }

    function ajaxSetLinkStatus(linkId, isActive) {
        let data = {};
        data['link_id'] = linkId;
        data['is_active'] = isActive;
        $.ajax({
            type: 'get',
            async: true,
            url: "{{ route('admin.instructors.set_course_link_access') }}",
            data: data,
            success: function (response) {
                if (response.success) {
                    let linkId = response.link_id;
                    let isActive = response.is_active;
                    SetIsActiveText(linkId, isActive);
                } else {
                    console.log("no success");
                    alert("@lang('labels.backend.instructors.access_modal.fail_message')");
                }
            }
        });
    }

    function SetIsActiveText(linkId,result) {
        let text = result == 1 ? "@lang('labels.general.active')" : "@lang('labels.general.inactive')";
        $("#isActive_"+linkId).text(text);
    }

    function setResultModalBody(text) {
        $("#linkResultBody").text(text);
    }
</script>
@endpush