@extends('backend.layouts.app')

@section('title', app_name() . ' | ' . __('labels.backend.conversation_editor.create'))

@section('content')

<div class="card">
    <div class="card-body">
    
        <div class="row">
            <div class="col-md-7">
                <h4 class="card-title mb-0" id="page_title">
                    {{ __('conversation_editor.conversation.title') }}
                </h4>
                <form id="form_saveScenario">
                    @csrf
                    <meta name="csrf-token" content="{{ Session::token() }}" />
                    @include('backend.scenarios.conversationEditor.conversation_details')
                </form>
            </div><!--col-->
            <div class="col-md-5">
                <img style="width:65%; margin-top:2em; float:none;" src="{{ asset('img/backend/Biomojo_Site_logo3_grey_long.png') }}" alt="BioMojo logo" align="right">
            </div>
        </div><!--row-->
        <hr class="thick mt-1">
        <div class="row mt-4">
            <div class="col">

                @include('backend.scenarios.conversationEditor.conversation_progress')

                <div id="conversation_quickstart">
                    @include('backend.scenarios.conversationEditor.conversation_quickstart')
                </div>
                <div id="conversation_location">
                    @include('backend.scenarios.conversationEditor.conversation_location')
                </div>
                <div id="conversation_character">
                    @include('backend.scenarios.conversationEditor.conversation_character')
                </div>
                <div id="conversation_supplemental_info">
                    @include('backend.scenarios.conversationEditor.conversation_supplement')
                </div>

                <div id="conversation_script">
                    @include('backend.scenarios.conversationEditor.conversation_script')
                </div>

                <div id="conversation_intents">
                    @include('backend.scenarios.conversationEditor.conversation_intents')
                </div>

                <div id="conversation_review">
                    @include('backend.scenarios.conversationEditor.conversation_review')
                </div>

            </div><!--col-->
        </div><!--row-->
    </div>
    
        @include('backend.scenarios.conversationEditor.conversation_buttons')  
        @include('backend.scenarios.includes.item_detail_modal_container')
        @include("backend.scenarios.conversationEditor.conversation_save_modal")

</div>
@endsection

