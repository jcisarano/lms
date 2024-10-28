
<div style="clear: both; display: none" class="conv-editor-section">
    <div class="ml-3">

        <h5 class="conv-section-title">{{ __('conversation_editor.conversation.steps.two') }}: {{ __('conversation_editor.conversation.character.title') }}</h5>
        <p class="conv-section-subtitle mb-3">{{ __('conversation_editor.conversation.character.subtitle') }}</p>
        
        <label for="char_gender" class="mr-2 mb-3">{{ __('conversation_editor.conversation.character.gender') }}</label>
        <select name="char_gender" id="char_gender" class="conv-char-filter mr-4 pl-1">
            <option value="ALL">{{ __('conversation_editor.conversation.character.dropdowns.all') }}</option>
            @foreach ($genders as $key=>$gender)
                <option value="@php echo $key @endphp">@php echo $gender @endphp</option>
            @endforeach
        </select>
       
        <label for="char_age_group" class="mr-2">{{ __('conversation_editor.conversation.character.age_group') }}</label>
        <select name="char_age_group" id="char_age_group" class="conv-char-filter pl-1">
            <option value="ALL">{{ __('conversation_editor.conversation.character.dropdowns.all') }}</option>
            @foreach ($ages as $key=>$age)
                <option value="@php echo $key @endphp">@php echo $age @endphp</option>
            @endforeach
        </select>
        
        <p class="pb-0 mb-1">{{ __('conversation_editor.conversation.character.select') }}</p>
        <p class="pb-0 mb-1 char_loc_fail_validation">{{ __('conversation_editor.conversation.character.select_warning') }}</p>
        
        <div class="location-container-inner m-0">
        
            <ul class="conv-gallery p-0">
                @for ($ii = 0; $ii < count($character_models); $ii++)
                    @php $char_id = $character_models[$ii]['id'];
                        $voice_service = $character_models[$ii]['voice']['provider'];
                        $voice_engine = $character_models[$ii]['voice']->getEngine();
                        $voice_id = $character_models[$ii]['voice']['name'];
                        $voice_pitch = $character_models[$ii]['voice_pitch_formatted'];
                        $voice_timbre = $character_models[$ii]['voice_timber_formatted'];
                        $voice_rate = $character_models[$ii]['voice_speed'];
                        $voice_lang = $character_models[$ii]['voice']['language_code'];
                        $gender_id = $character_models[$ii]['gender'];
                        $age_id = $character_models[$ii]['age'];
                    @endphp
                    
                    <li class='m-0 conv-loc-gallery-item conv-gallery-item item_inner_container  @php echo "gender_{$gender_id} age_{$age_id}" @endphp'  id="conv-gallery-@php echo $char_id @endphp" data-item_id="@php echo $char_id @endphp">
                          
                        @if(count($character_models[$ii]['images']) > 0)
                        <img src="@php echo $character_models[$ii]['images'][0]->simple_path @endphp" alt="" class="img-fluid conv-loc-img mb-2" />
                        @else
                        <img src="/img/backend/conversation_editor/character_placeholder.png" alt="" class="img-fluid conv-loc-img mb-2" />
                        @endif                               

                        <span>
                            <p style="margin: 0 -15px 0.5em -15px; width: 100%; text-align:left;">@php echo $character_models[$ii]["character_model_name"] @endphp</p>
                            <p style="margin-left: -15px; float: left;">{{ __('conversation_editor.conversation.character.hear_voice') }}</p>
                            
                            <button type="button" class="btn play-voice-sample-button voice-sample-button" data-voice_pitch="@php echo $voice_pitch @endphp%" data-voice_timbre="@php echo $voice_timbre @endphp%" data-voice_rate="@php echo $voice_rate @endphp%" data-voice_service="@php echo $voice_service @endphp" data-voice_engine="@php echo $voice_engine @endphp" data-voice_id="@php echo $voice_id @endphp" data-voice_lang="@php echo $voice_lang @endphp" data-voice_sample="{{ __('conversation_editor.conversation.item_details.character.voice_sample') }}">></button>
                            <button type="button" class="mb-0 btn conv-blue btn-block font-weight-normal p-0 character-preview-button" style="height:2em;" data-item_id="@php echo $char_id @endphp" data-modal_id="itemDetailModal" data-item_type="characterModels">{{ __('conversation_editor.conversation.character.buttons.details') }}</button>
                        </span>
                    
                  </li>
                @endfor  
            </ul>
        
        </div>
    </div>
</div>

@push('after-scripts')
<script>
    var last_selected_gender;
    var last_selected_age;
    $(".character-preview-button").on("click", function(event) {
        event.stopPropagation();

        let button = $(this);
        setItemSelectAction(function() { 
            handleCharacterSelect(button);
        });
        getDetailsForModal(button);
    });
    
    $("#char_gender").change(function() {
        selected = ".gender_" + this.value
        
        age_selector = "";
        if (last_selected_age != null) {
            age_selector = "" + last_selected_age + "";
        }
          
        filterCharacterView( selected, age_selector, last_selected_gender != null)
        
        last_selected_gender = this.value != "ALL" ? selected : null;
    });
    
        
    $("#char_age_group").change(function() {
        selected = ".age_" + this.value
        
        gender_selector = "";
        if (last_selected_gender != null) {
            gender_selector = "" + last_selected_gender;
        }
        
        filterCharacterView( selected, gender_selector, last_selected_age != null)
        last_selected_age = this.value != "ALL" ? selected : null;        
    });    
    
    
    function filterCharacterView(primarySelector, secondarySelector, showBeforeHide) {
        if (primarySelector.includes("ALL")) {
            $(".conv-gallery-item:hidden"+secondarySelector).show(500);
            return;
        }
        
        if (showBeforeHide) {
            $(".conv-gallery-item:hidden"+secondarySelector).show(500, function(){
                $(".conv-gallery-item:not(" + primarySelector + ")").hide(500);
            });            
        } else {
            $(".conv-gallery-item:not(" + primarySelector + ")").hide(500);
        }        
    }

    $("body").on("click", ".conv-gallery-item", function(event) {
        div = $(this)
        handleCharacterSelect(div);
    });

    function handleCharacterSelect(bb) {
        scenario_character_model_id = bb.data('item_id');
        setCharacterModelSelected(scenario_character_model_id);
    }    

    function setCharacterModelSelected(char_model_id)
    {
        $(".conv-loc-gallery-item").removeClass("selected_gallery_item");
        $("#conv-gallery-" + char_model_id).addClass("selected_gallery_item");
        
        $(".char_loc_fail_validation").hide();
    }

    $("body").on("click", ".play-voice-sample-button", function(event) {
        event.stopPropagation();
        
        button = $(this);
        playSound(button);
    });

    function playSound(button) {
        provider = button.data("voice_service");
        engine = button.data("voice_engine");
        voiceId = button.data("voice_id");
        targetLang = button.data("voice_lang");
        pitch = button.data("voice_pitch");
        rate = button.data("voice_rate");
        sample = button.data("voice_sample");
        timbre = button.data("voice_timbre");

        PlayVoiceSample({provider: provider, voiceId: voiceId, targetLang: targetLang, 
        pitch: pitch, rate: rate, sampleText: sample, timbre: timbre, engine:engine});
    }
</script>

<script type="text/javascript" src="/js/voice_sample.js"></script>
@endpush
