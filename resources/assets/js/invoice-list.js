(function () {
    "use strict"

    console.log("");

    //delete Btn
    let invoicebtn = document.querySelectorAll(".invoice-btn");
    invoicebtn.forEach((eleBtn) => {
        eleBtn.onclick = () => {
            let invoice = eleBtn.closest(".invoice-list")
            invoice.remove();
        }
    })    
    
    /* For Date Range Picker */
        flatpickr("#daterange", {
            mode: "range",
            dateFormat: "Y-m-d",
        });
    
})();
