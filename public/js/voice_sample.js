
var xhr;

function PlayVoiceSample({provider, voiceId, targetLang, pitch, rate, sampleText, timbre, engine} = {}) {
    if (xhr) {
        return;
    }

    formattedData = JSON.stringify({"voiceId":voiceId,
                                    "targetLang":targetLang,
                                    "pitch":pitch,
                                    "speed":rate,
                                    "timbre":timbre,
                                    "provider":provider,
                                    "engine":engine,
                                    "textLang":"en-US",
                                    "text": sampleText,
                                    "textType":"ssml",
                                    "audioType":"mp3"});

    xhr = $.ajax({
        url: "/admin/textToSpeech/formatAndConvert",
        type: "POST",
        contentType: 'application/json',
        data: formattedData,
        statusCode: {
            419: function() {
                alert("Session timed out, refresh page.");
            }
        }
    });

    xhr.onDone = function( jsonData ) {
        if (jsonData.success == 1 ) {
            file_path = "/storage/voice/" + jsonData.audio
            audio_player = new Audio(file_path);
            audio_player.play();
        } else {
            alert(jsonData.response_msg);
        }
    };

    xhr.onFail = function(status) {
       //mssg = 'Failed to retrieve sample voice file (';
       //alert(mssg + status + ')');
    };

    xhr.done(function(response) {
        if( response != null ) {
            if( typeof xhr.onDone === "function" ) {
                xhr.onDone( response );
            }
        } else {
            xhr.onFail("Empty response");
        }
    }).fail(function(jqXHR, status, error) {
        if( typeof xhr.onFail === "function" ) {
            xhr.onFail(error);
        }
    }).always(function() {
        xhr = null;
    });
}