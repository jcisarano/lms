@extends('backend.layouts.app')

@section('title', app_name() . ' | ' . __('menus.backend.instructors.seat_index'))

@section('content')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/dt-1.10.23/datatables.min.css" />

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-sm-5">
                <h4 class="card-title mb-0">
                    {{ __('menus.backend.instructors.seat_index') }}
                </h4>
            </div><!-- col-sm-5 -->
        </div><!--row-->


        <div class="row mt-4">
            <div class="col">
                <div>
                    @include('backend.instructors.includes.seats')
                    <p>Next billing date: {{$nextBillDate}}</p>
                    <p>Next billing amount: {{$nextBillAmount}}</p>
                    <div>
                        <button data-toggle="modal" data-target="#addSeats" class="btn conv-blue conv-next-btn">Add more seats</button>
                        <button data-toggle="modal" data-target="#dropSeats"  class="btn conv-blue conv-next-btn">Drop seats</button>
                    </div>
                </div>
            </div><!--col-->
        </div><!--row-->
        
        <div class="row mt-4">
            <div class="col">
                <div style="border:1px solid red;">
                    <h4 class="card-title mb-0">Testing tools</h4>
                    <button id="btn_expire">Expire my seats now</button>
                    <button>Other dev thing</button>
                    </div>
                </div>
            </div><!--col-->
        </div><!--row-->
    </div><!--card-body-->
</div><!--card-->

<div class="modal fade" id="dropSeats">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">@lang('labels.backend.instructors.drop_seats_modal.title')</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>
            <div class="modal-body">
                <div>You currently have XX free seats. If you wish to drop more seats than that, you must remove the students from those seats.</div>
                <div><select><option>Select quantity</option><option>10</option><option>20</option><option>30</option></select></div>
            </div>
            <div class="modal-footer">
                <button type="button" id="btn_removeStudent" class="btn btn-primary" data-dismiss="modal">@lang('labels.backend.instructors.access_modal.button_apply')</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('labels.backend.instructors.access_modal.button_cancel')</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addSeats">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">@lang('labels.backend.instructors.add_seats_modal.title')</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>
            <div class="modal-body">
                <div>Add more seats for your students.</div>
                <div>You currently have XX free seats.</div>
                <div><select><option>Select quantity</option><option>10</option><option>20</option><option>30</option></select></div>
                <div>Cost: money</div>
            </div>
            <div class="modal-footer">
                <button type="button" id="btn_purchaseSeats" class="btn btn-primary" data-dismiss="modal">@lang('labels.backend.instructors.access_modal.button_apply')</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('labels.backend.instructors.access_modal.button_cancel')</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('after-scripts')
<script type="text/javascript" src="https://cdn.datatables.net/v/bs4/dt-1.10.23/datatables.min.js"></script>

<script>
    var freeSeats = {{$freeSeats}};
    var totalSeats = {{$totalSeats}};
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $(document).ready(function () {
        console.log("do stuff here");
    });

    $(document.body).on("click", "#btn_expire", function (){ alert("expire these seats"); });
    //$(document.body).on('click', '.btn_allowStudent', function () {
    //    let studentId = $(this).data('id');
    //    GiveStudentAccess(studentId);
    //});

    //$(document.body).on('click', '#btn_removeStudent', function () {
    //    let studentId = $(this).data('id');
    //    let data = {};
    //    data['studentId'] = studentId;
    //    $.ajax({
    //        type: 'get',
    //        async: true,
    //        url: "{{ route('admin.instructors.remove_student_connection') }}",
    //        data: data,
    //        success: function (response) {
    //            if (response.success) {
    //                $("#studentTable").DataTable().ajax.reload();
    //                $("#instructorStudentResultModal").modal('toggle');
    //            } else {
    //                alert("@lang('labels.backend.instructors.access_modal.fail_message')");
    //            }
    //        }
    //    });
    //});

    //function ajaxSetAccess(studentId, studentCanAccess) {
    //    let data = {};
    //    data['studentId'] = studentId;
    //    data['canAccess'] = studentCanAccess;
    //    $.ajax({
    //        type: 'get',
    //        async: true,
    //        url: "{{ route('admin.instructors.set_student_course_access') }}",
    //        data: data,
    //        success: function (response) {
    //            if (response.success) {
    //                let studentId = response.student_id;
    //                let canAccess = response.canAccess;
    //                SetCanAccessText(studentId, canAccess);
    //            } else {
    //                alert("@lang('labels.backend.instructors.access_modal.fail_message')");
    //            }
    //        }
    //    });
    //}
</script>

@endpush