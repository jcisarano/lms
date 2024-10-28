<div class="btn-group" role="group" aria-label="@lang('labels.backend.scenarios.table.actions')">

    &nbsp;&nbsp;
    <a href="#"
       onclick="return false;"
       data-toggle="tooltip"
       data-placement="top"
       title="@lang('labels.backend.instructors.link.activate')"
       class="btn green-button btn_activateLink"
       data-id="{{ $link->id }}">
       <i class="fas fa-thumbs-up"></i>
    </a>

    &nbsp;&nbsp;
    <a href="#"
       onclick="return false;"
       data-toggle="tooltip"
       data-placement="top"
       title="@lang('labels.backend.instructors.link.deactivate')"
       class="btn grey-button btn_deactivateLink"
       data-id="{{ $link->id }}">
       <i class="fas fa-thumbs-down"></i>
    </a>

    &nbsp;&nbsp;
    <a href="#"
       onclick="return false;"
       data-toggle="tooltip"
       data-placement="top"
       title="@lang('labels.backend.instructors.link.delete')"
       class="btn red-button btn_deleteLink"
       data-id="{{ $link->id }}">
       <i class="fas fa-trash"></i>
    </a>

    &nbsp;&nbsp;
    <a href="#"
       onclick="return false;"
       data-toggle="tooltip"
       data-placement="top"
       title="@lang('labels.backend.instructors.link.copy')"
       class="btn darkblue-button btn_copyLink"
       data-link="{{URL::to("/join/{$link->uuid}")}}">
       <i class="fas fa-clipboard"></i>
    </a>

</div>


