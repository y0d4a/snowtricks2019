/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you require will output into a single css file (app.css in this case)
require("../css/app.css");
require("@fortawesome/fontawesome-free/css/all.min.css");
require("@fortawesome/fontawesome-free/js/all.js");

const $ = require("jquery");
// this "modifies" the jquery module: adding behavior to it
// the bootstrap module doesn't export/return anything
require("bootstrap");

// or you can include specific pieces
// require('bootstrap/js/dist/tooltip');
// require('bootstrap/js/dist/popover');

$(document).ready(function() {
    $("[data-toggle='popover']").popover();
});

// Need jQuery? Install it with "yarn add jquery", then uncomment to require it.
// const $ = require('jquery');

console.log("Hello Webpack Encore! Edit me in assets/js/app.js");

$(function () {
    $("#trickList").on("shown.bs.collapse", function (e) {
        $("html,body").animate({
            scrollTop: $("#trickList").offset().top -80
        }, 500);
    });
});

// Modale management

$("#trickModal").on("show.bs.modal", function (event) {
    var button = $(event.relatedTarget); // Button that triggered the modal
    var id= button.data("whatever"); // Extract info from data-* attributes
    // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
    // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
    var modal = $(this);
    var user= $(this).data("user");


    $.ajax({
        type: "POST",
        url: "/trick/"+id,
        success: function(response)
        {
            var datepost = new Date(response.datePost.timestamp*1000).toLocaleDateString();
            var dateedit = new Date(response.dateUpdate.timestamp*1000).toLocaleDateString();

            console.log(response.description);
            if (user === response.Author.id){
                $("#buttons").show();
            }
            $("#trick-title").html(response.title);
            $("#trick-description").html(response.description);
            $("#trick-author").html(response.Author.username);
            $("#trick-editor").html(response.Editor.username);
            $("#trick-date-post").html(datepost);
            $("#trick-date-update").html(dateedit);
            $("#trick-category").html(response.category);
        }
    });
});

$("#trickModal").on("hidden.bs.modal", function(){
    $("#buttons").hide();
});