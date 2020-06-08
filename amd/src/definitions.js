define(['jquery','core/log'], function($,log) {
    "use strict"; // jshint ;_;

/*
This file contains class and ID definitions.
 */

    log.debug('Poodll Time definitions: initialising');

    return{
        component: 'mod_poodlltime',
        componentpath: 'mod/poodlltime',
        //player code
        hiddenplayer: 'mod_poodlltime_hidden_player',
        hiddenplayerbutton: 'mod_poodlltime_hidden_player_button',
        hiddenplayerbuttonactive: 'mod_poodlltime_hidden_player_button_active',
        hiddenplayerbuttonpaused: 'mod_poodlltime_hidden_player_button_paused',
        hiddenplayerbuttonplaying: 'mod_poodlltime_hidden_player_button_playing',
        qr_player: 'mod_poodlltime_qr_player',

        //popover
        okbuttonclass: 'mod_poodlltime_quickgrade_ok',
        ngbuttonclass: 'mod_poodlltime_quickgrade_ng',
        quickgradecontainerclass: 'mod_poodlltime_quickgrade_cont',

        //grade now
        passagecontainer: 'mod_poodlltime_grading_passagecont',
        audioplayerclass: 'mod_poodlltime_grading_player',
        wordplayerclass: 'mod_poodlltime_hidden_player',
        wordclass: 'mod_poodlltime_grading_passageword',
        spaceclass: 'mod_poodlltime_grading_passagespace',
        badwordclass: 'mod_poodlltime_grading_badword',
        endspaceclass: 'mod_poodlltime_grading_endspace',
        unreadwordclass:  'mod_poodlltime_grading_unreadword',
        unreadspaceclass: 'mod_poodlltime_grading_unreadspace',
        wpmscoreid: 'mod_poodlltime_grading_wpm_score',
        accuracyscoreid: 'mod_poodlltime_grading_accuracy_score',
        sessionscoreid: 'mod_poodlltime_grading_session_score',
        errorscoreid: 'mod_poodlltime_grading_error_score',
        errorrateid: 'mod_poodlltime_grading_errorrate_score',
        scrateid: 'mod_poodlltime_grading_scrate_score',
        formelementwpmscore: 'mod_poodlltime_grading_form_wpm',
        formelementaccuracy: 'mod_poodlltime_grading_form_accuracy',
        formelementsessionscore: 'mod_poodlltime_grading_form_sessionscore',
        formelementendword: 'mod_poodlltime_grading_form_sessionendword',
        formelementtime: 'mod_poodlltime_grading_form_sessiontime',
        formelementerrors: 'mod_poodlltime_grading_form_sessionerrors',
        modebutton: 'mod_poodlltime_modebutton',

        //activity
        passagefinished: 'mod_poodlltime_passage_finished',
        spotcheckmodebutton: 'mod_poodlltime_spotcheckbutton',
        transcriptmodebutton: 'mod_poodlltime_transcriptcheckbutton',
        gradingmodebutton: 'mod_poodlltime_gradingbutton',
        clearbutton: 'mod_poodlltime_clearbutton',
        spotcheckmode: 'mod_poodlltime_spotcheckmode',
        aiunmatched: 'mod_poodlltime_aiunmatched',

        //quiz
        qtype_pictureprompt: '1',
        qtype_audioprompt: '2',
        qtype_textpromptlong: '4',
        qtype_textpromptshort: '5',
        qtype_textpromptaudio: '6',
        qtype_dictationchat: '8',

        //running records features
        maybeselfcorrectedwordclass: 'mod_poodlltime_grading_maybeselfcorrectedword',
        selfcorrectedwordclass: 'mod_poodlltime_grading_selfcorrectedword',
        structuralclass: 'mod_poodlltime_grading_structural',
        meaningclass: 'mod_poodlltime_grading_meaning',
        visualclass: 'mod_poodlltime_grading_visual',
        notesclass: 'mod_poodlltime_notes',

        //modes
        modegrading: 'grading',
        modespotcheck: 'spotcheck',
        modetranscript: 'transcript',
        modemsv: 'msv',

        //MSV stuff
        msvclosebuttonclass: 'mod_poodlltime_msvgrade_close',
        s_buttonclass: 'mod_poodlltime_msv_s',
        m_buttonclass: 'mod_poodlltime_msv_m',
        v_buttonclass: 'mod_poodlltime_msv_v',
        correctbuttonclass: 'mod_poodlltime_msv_correct',
        errorbuttonclass: 'mod_poodlltime_msv_error',
        selfcorrectbuttonclass: 'mod_poodlltime_msv_selfcorrect',
        msvcontainer: 'mod_poodlltime_msv_cont',
        msvmodebutton: 'mod_poodlltime_msvbutton',
        msvgradebutton: 'mod_poodlltime_msvgrade_msv',
        msvmode: 'mod_poodlltime_msvmode',
        stateerror: 'stateerror',
        statecorrect: 'statecorrect',
        stateselfcorrect: 'stateselfcorrect',
        formelementnotes: 'mod_poodlltime_grading_form_notes',
        formelementselfcorrections: 'mod_poodlltime_grading_form_selfcorrections',
        gradingmode: 'mod_poodlltime_gradingmode',
        transcriptmode: 'mod_poodlltime_transcriptmode',
        msvcontainerclass: 'mod_poodlltime_msvcontainer',
        msvbuttonsbox: 'mod_poodlltime_msvbuttonsbox',

        //print attempt
        printattemptcontainer: 'mod_poodlltime_printattempt_cont',
        printattemptresults: 'mod_poodlltime_printattempt_results',
        printattemptdetails: 'mod_poodlltime_printattempt_details'

    };//end of return value
});