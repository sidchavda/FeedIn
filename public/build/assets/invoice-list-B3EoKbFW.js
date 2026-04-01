(function(){console.log(""),document.querySelectorAll(".invoice-btn").forEach(e=>{e.onclick=()=>{e.closest(".invoice-list").remove()}}),flatpickr("#daterange",{mode:"range",dateFormat:"Y-m-d"})})();
