<div class="btn-group" role="group" aria-label="@lang('labels.backend.scenarios.table.actions')">

    &nbsp;&nbsp;
    <a href="#"
       onclick="return false;"
       data-toggle="tooltip"
       data-placement="top"
       title="@lang('labels.backend.instructors.student.allow')"
       class="btn green-button btn_allowStudent"
       data-id="{{ $student->id }}">
       <i class="fas fa-thumbs-up"></i>
    </a>

    &nbsp;&nbsp;
    <a href="#"
       onclick="return false;"
       data-toggle="tooltip"
       data-placement="top"
       title="@lang('labels.backend.instructors.student.deny')"
       class="btn grey-button btn_denyStudent"
       data-id="{{ $student->id }}">
       <i class="fas fa-thumbs-down"></i>
    </a>

    &nbsp;&nbsp;
    <a href="#"
       onclick="return false;"
       data-toggle="tooltip"
       data-placement="top"
       title="@lang('labels.backend.instructors.student.delete')"
       class="btn red-button btn_deleteStudent"
       data-id="{{ $student->id }}">
       <i class="fas fa-trash"></i>
    </a>

</div>


