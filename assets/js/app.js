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


//-------------------------------- TO SHOW DOC NAME ---------------------------
$(document).ready(function() {
    $("[data-toggle='popover']").popover();

    if(document.getElementById("image_name")){
        var input = document.getElementById("image_name");
    } else if(document.getElementById("profile_picture_profilePicture")){
        var input = document.getElementById("profile_picture_profilePicture");
    } else if(document.getElementById("default_profile_picture_default_profile_picture")){
        var input = document.getElementById("default_profile_picture_default_profile_picture");
    }else{
        var input = document.getElementById("default_trick_picture_default_trick_picture");
    }

    var placeholder = $(".custom-file-label");

    input.addEventListener(
        "change",
        function ()
        {
            var value = input.value.replace('C:\\fakepath\\', '').trim();
            placeholder.html(value);
        }
    );
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
