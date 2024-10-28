
<div style="clear: both; display: none" class="conv-editor-section">
    <div class="ml-3">
        
        <h5 class="conv-section-title">{{ __('conversation_editor.conversation.steps.one') }}: {{ __('conversation_editor.conversation.location.title') }}</h5>
        <p class="conv-section-subtitle">{{ __('conversation_editor.conversation.location.subtitle') }}</p>
        <p class="pb-0 mb-1">{{ __('conversation_editor.conversation.location.select') }}</p>
        <p class="pb-0 mb-1 char_loc_fail_validation">{{ __('conversation_editor.conversation.location.select_warning') }}</p>
        <div class="location-container-inner m-0">
        
            <ul class="conv-gallery p-0">
                @for ($ii = 0; $ii < count($locations); $ii++)
                  <li class="m-0 conv-loc-gallery-item loc-gallery-item item_inner_container"  id="loc-gallery-@php echo $locations[$ii]['id'] @endphp" data-item_id="@php echo $locations[$ii]['id'] @endphp">
                          
                    @if(count($locations[$ii]['images']) > 0)
                        <img src="@php echo $locations[$ii]['images'][0]->simple_path @endphp" alt="" class="img-fluid conv-loc-img mb-2" />
                    @else
                        <img src="/img/backend/conversation_editor/location_placeholder.png" alt="" class="img-fluid conv-loc-img mb-2" />
                    @endif
                          
                    <span>
                        <p style="margin: 0 -15px 0.5em -15px;">@php echo $locations[$ii]['location_name'] @endphp</p>
                        <button type="button" class="mb-0 btn conv-blue btn-block font-weight-normal p-0 location-preview-btn" style="height:2em;" data-item_id="@php echo $locations[$ii]['id'] @endphp" data-modal_id="itemDetailModal" data-item_type="locations">{{ __('conversation_editor.conversation.location.buttons.details') }}</button>
                    </span>
                    
                  </li>
                @endfor  
            </ul>
            
        </div>
        
    </div>    
</div>

@push('after-scripts')
<script>
    $("body").on("click", ".location-preview-btn", function(event) {
        event.stopPropagation();
        
        var button = $(this);
        setItemSelectAction(function() { 
            handleLocationSelect(button);
        });
        getDetailsForModal(button);
    });
    
    $("body").on("click", ".loc-gallery-item", function() {
        div = $(this);
        handleLocationSelect(div);
    });
        
    function setLocationSelected(location_id)
    {
        $(".conv-loc-gallery-item").removeClass("selected_gallery_item");
        $("#loc-gallery-" + location_id).addClass("selected_gallery_item");
        
        $(".char_loc_fail_validation").hide();
    }
    
    function handleLocationSelect(bb) {
        scenario_location_id = bb.data('item_id');
        setLocationSelected(scenario_location_id);
    }
</script>
@endpush
