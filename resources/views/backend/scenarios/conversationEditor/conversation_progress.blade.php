<div id="progress-display" style="display:none;">
    <h4 class="progress-label">{{ __('conversation_editor.conversation.progress') }}</h4>
    <ul class="nav nav-pills" id="progress-button-list">
      <li id="progress-li-1"><span class="tooltip-text" data-toggle="tooltip" title="{{ __('conversation_editor.conversation.location.title') }}">1</span></li>
      <li id="progress-li-2"><span class="tooltip-text" data-toggle="tooltip" title="{{ __('conversation_editor.conversation.character.title') }}">2</span></li>
      <li id="progress-li-3"><span class="tooltip-text" data-toggle="tooltip" title="{{ __('conversation_editor.conversation.supplemental_info.title') }}">3</span></li>
      <li id="progress-li-4"><span class="tooltip-text" data-toggle="tooltip" title="{{ __('conversation_editor.conversation.script.title') }}">4</span></li>
      <li id="progress-li-5"><span class="tooltip-text" data-toggle="tooltip" title="{{ __('conversation_editor.conversation.intents.title') }}">5</span></li>
      <li id="progress-li-6"><span class="tooltip-text" data-toggle="tooltip" title="{{ __('conversation_editor.conversation.review.title') }}">6</span></li>
    </ul>
</div>

@push('after-scripts')
<script>
    $(document).ready(function(){
        $("#progress-button-list > li").click(function(event) {
            button = $(this);
            if (button.hasClass("active")) {
                new_section_id = button.text()
                if (new_section_id != current_section_id) {
                    if (validateCurrentSection()) {
                        processCurrentSection();

                        $(editor_sections[current_section_id] + " " + editor_section_class).hide();
                        current_section_id = parseInt(new_section_id);

                        $(editor_sections[current_section_id] + " " + editor_section_class).show();
                        InitSection(current_section_id);

                        setProgressDisplayVisibility(current_section_id);
                    }
                }
            }
        });
    });

    function setProgressDisplayVisibility( sectionIndex ) {
        if (sectionIndex == 0) {
            $("#progress-display").hide();
        }
        else {
            $("#progress-display").show();
        }
        
        updateActiveProgressSteps( sectionIndex );
    }

    var maxSelected = 0;
    function updateActiveProgressSteps(currentSectionIndex) {
        maxSelected = Math.max(maxSelected, currentSectionIndex);

        for (var ii = 0; ii <= 6; ii++) {
            var element = $("#progress-li-"+ii)
            
            if (ii <= maxSelected) {
                element.addClass("active")
                if (ii == currentSectionIndex) {
                    element.addClass("curr_step");
                } else {
                    element.removeClass("curr_step");
                }                  
            } else {
                element.removeClass("active");
                element.removeClass("curr_step");
            }
        }
    }
</script>
@endpush
