define(['jquery', 'core/log', 'mod_poodlltime/definitions', 'mod_poodlltime/pollyhelper'], function($, log, def, polly) {
  "use strict"; // jshint ;_;

  /*
  This file is to manage the quiz stage
   */

  log.debug('Poodll Time Multichoice: initialising');

  return {

    init: function(index, itemdata, quizhelper) {
      this.register_events(index, itemdata, quizhelper);
    },

    prepare_html: function(itemdata) {
      //do something
    },

    register_events: function(index, itemdata, quizhelper) {
      //When click next button , report and leave it up to parent to eal with it.
      $("#" + itemdata.uniqueid + "_container .poodlltime_nextbutton").on('click', function(e) {
        var stepdata = {};
        stepdata.index = index;
        stepdata.hasgrade = true;
        stepdata.totalitems=4;
        stepdata.correctitems=2;
        stepdata.grade = 50;

        quizhelper.do_next(stepdata);
      });
      $("#" + itemdata.uniqueid + "_container ."+itemdata.uniqueid+"_option").on('click', function(e) {
        $("."+itemdata.uniqueid+"_option").prop("disabled",true);
        $("."+itemdata.uniqueid+"_fb").html("<i style='color:red;' class='fa fa-times'></i>");
        $("."+itemdata.uniqueid+"_option"+itemdata.correctanswer+"_fb").html("<i style='color:green;' class='fa fa-check'></i>");
        console.log(itemdata);
      });
    }

  }; //end of return value
});