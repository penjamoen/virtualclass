$(document).ready(function() { // This script need be improved
  my_protocol = location.protocol;
  my_pathname=location.pathname;
  work_path = my_pathname.substr(0,my_pathname.indexOf('/courses/'));
  $.ajax({
    contentType: "application/x-www-form-urlencoded",
    beforeSend: function(content_object) {},
    type: "POST",
    url: my_protocol+"//"+location.host+work_path+"/main/glossary/glossary_ajax_request.php",
    data: "glossary_data=true",
    success: function(response) {
      if (response.length==0) {
        return false;
      }

      data_terms=response.split("[|.|_|.|-|.|]");
      for(i=0;i<data_terms.length;i++) {
        specific_terms=data_terms[i].split("__|__|");
        var real_term = specific_terms[1];
        var real_code = specific_terms[0];
        $('#content').removeHighlight().highlight(real_term,real_code);
      }
    //mouse over event
    $("#content .glossary-ajax").mouseover(function(){
      random_id=Math.round(Math.random()*100);
      div_show_id="div_show_id"+random_id;
      div_content_id="div_content_id"+random_id;
      $(this).append("<div id="+div_show_id+" ><div id="+div_content_id+">&nbsp;</div></div>");
      $("div#"+div_show_id).attr("style","display:inline;float:left;position:absolute;background-color:#F2F2F2;border-bottom: 1px solid #2E2E2E;border-right: 1px solid #2E2E2E;border-left: 1px solid #2E2E2E;border-top: 1px solid #2E2E2E;color:#305582;margin-left:5px;margin-right:5px;");
      $("div#"+div_content_id).attr("style","background-color:#F2F2F2;color:#0B3861;margin-left:8px;margin-right:8px;margin-top:5px;margin-bottom:5px;");
      notebook_id = $(this).attr("name");
      data_notebook = notebook_id.split("link");
      my_glossary_id=data_notebook[1];
      $.ajax({
        contentType: "application/x-www-form-urlencoded",
        beforeSend: function(content_object) {
          $("div#"+div_content_id).html("<img src="+my_protocol+"//"+location.host+work_path+"/main/inc/lib/javascript/indicator.gif />");
        },
        type: "POST",
        url: my_protocol+"//"+location.host+work_path+"/main/glossary/glossary_ajax_request.php",
        data: "glossary_id="+my_glossary_id,
        success: function(response) {
          $("div#"+div_content_id).html(response);
        }
      });
    });
    //mouse out event
    $("#content .glossary-ajax").mouseout(function(){
      var current_element;
      current_element = $(this);
      div_show_id=current_element.find("div").attr("id");
      $("div#"+div_show_id).remove();
    });
   }
  });
});