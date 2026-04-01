(function () {
    'use strict'

    // Date issued 
    flatpickr("#invoice-date-issued", {});

    // Due date 
    flatpickr("#invoice-date-due", {});

    // for nummber of products selected 
    let index = 0
    document.querySelector(".invoice-add-item").onclick = () => {
        let element = `<tr class="invoice-list"> 
        <td>
            <input type="number" class="form-control form-control-lg" placeholder="1">
        </td>
        <td>
            <input type="text" class="form-control form-control-lg" placeholder="Product Nme">
        </td>
        <td class="invoice-quantity-container">
            <div class="input-group border rounded flex-nowrap">
                <button class="btn btn-icon btn-primary input-group-text flex-fill product-quantity-minus"><i class="ri-subtract-line"></i></button>
                <input type="text" class="form-control form-control-sm border-0 text-center w-100" aria-label="quantity" id="product-quantity4" value="1">
                <button class="btn btn-icon btn-primary input-group-text flex-fill product-quantity-plus"><i class="ri-add-line"></i></button>
            </div>
        </td>
        <td><input class="form-control form-control-lg" placeholder="" type="number"></td>
        <td><input class="form-control form-control-lg" placeholder="Total Amount" type="text" ></td>
        <td>
            <button class="invoice-btn btn btn-sm btn-icon btn-danger-light"><i class="ri-delete-bin-5-line"></i></button>
        </td>
    </tr> `
        document.querySelector(".invoice-body").innerHTML += element
        index = index + 1
    }

    var minusBtn = document.querySelectorAll(".quantity-minus"),
        PlusBtn = document.querySelectorAll(".quantity-plus"),
        
       
        minValue = 0,
        maxValue = 30;


    minusBtn.forEach((eleBtn1) => {
        eleBtn1.onclick = function (e) {
            let value =  e.currentTarget.nextElementSibling.value
            if (value > minValue) {
                value = value - 1;
                e.currentTarget.nextElementSibling.value=value;
            }
        }
    })
    PlusBtn.forEach((eleBtn2) => {
        eleBtn2.onclick = function (e) {
            let value =  e.currentTarget.previousElementSibling.value
            if (value < maxValue) {
                value = Number(value) + 1;
                e.currentTarget.previousElementSibling.value=value;
            }
        }
    })


    /* Start::Choices JS */
    document.addEventListener('DOMContentLoaded', function () {
        var genericExamples = document.querySelectorAll('[data-trigger]');
        for (let i = 0; i < genericExamples.length; ++i) {
            var element = genericExamples[i];
            new Choices(element, {
                allowHTML: true,
                searchEnabled: false,
                removeItemButton: true,
            });
        }
    });


    //delete Btn
    let invoicebtn = document.querySelectorAll(".invoice-btn");

    invoicebtn.forEach((eleBtn) => {
        eleBtn.onclick = () => {
            let invoice = eleBtn.closest(".invoice-list")
            invoice.remove();
        }
    })

let GuantityPlus = (ele) => {
}


let changeTheInfo = (title, name, address, address1, id, create, due) => {
    document.querySelector(".invoice-title").innerHTML = title,
        document.querySelectorAll(".company-name").forEach((companyval) => {
            companyval.value = name
        }),
        document.querySelectorAll(".company-address").forEach((companyadr) => {
            companyadr.value = address
        }),
        document.querySelectorAll(".company-address1").forEach((companyadr1) => {
            companyadr1.value = address1
        }),
        document.querySelector(".invoice-id").value = id,
        document.querySelector(".create-date").value = create,
        document.querySelector(".due-date").value = due
}

document.getElementById("invoice-create").onclick = () => {
    document.querySelector(".invoice-title").innerHTML = "Create Invoice"

}
let invoicePrint = (ele)=>{
    document.querySelector(".invoice-edit").click()
    setTimeout(()=>{
        window.print()
    },100)
}

})();