jQuery(document).ready(function(){
     // Clear vote functionality
     jQuery(document).on("click", ".clear-vote", function(e){
          e.preventDefault();
          var user_id = jQuery(this).attr("data-user-id");
          var post_id = jQuery(this).attr("data-post-id");
          var nonce = jQuery(this).attr("data-nonce");
          var clear_text = jQuery(this).text();
          
          //jQuery(".status-" + post_id).html("&nbsp;&nbsp;").addClass("loading-img").show();
          
          jQuery.ajax({
               type : "post",
               dataType : "json",
               async: false,
               url : wtilp.ajax_url,
               data : {action: "wti_like_post_clear_vote", user_id : user_id, post_id : post_id, nonce: nonce},
               success: function(response) {
                    if(response.error == 0){
                         // Show the success message
                         jQuery(".clear-" + post_id).html(response.msg);
                    } else {
                         alert(response.msg);
                    }
               }
          });
     });
});