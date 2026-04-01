(function () {
    'use strict'

    let checkAll = document.querySelector('.check-all');
    checkAll.addEventListener('click', checkAllFn)

    function checkAllFn() {
        if (checkAll.checked) {
            document.querySelectorAll('.invoice-checkbox input').forEach(function (e) {
                e.closest('.invoice-list').classList.add('selected');
                e.checked = true;
            });
        }
        else {
            document.querySelectorAll('.invoice-checkbox input').forEach(function (e) {
                e.closest('.invoice-list').classList.remove('selected');
                e.checked = false;
            });
        }
    }


    //For Date Range Picker
    // flatpickr("#daterange", {
    //     mode: "range",
    //     dateFormat: "d-m-y",
    // });
    
    // Date issued 
    // flatpickr("#invoice-date-issued", {});

    // Due date 
    // flatpickr(".invoice-date-due", {});


    // for nummber of products selected 
    let index = 0
    document.querySelectorAll(".invoice-add-item").forEach(button => {
        button.onclick = () => {
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
        // document.querySelector(".invoice-body").innerHTML += element
        // index = index + 1

        // Assuming you have multiple elements with class "invoice-body"
        let invoiceBodies = document.querySelectorAll(".invoice-body");

        // Iterate through each invoice body element
        invoiceBodies.forEach(invoiceBody => {
            // Create a new table row element
            let newRow = document.createElement('tr');
            newRow.classList.add('invoice-list');
            // Set the innerHTML of the new row
            newRow.innerHTML = element;
            // Append the new row to the current invoice body
            invoiceBody.appendChild(newRow);
        });
    }});

    // for nummber of products selected 
    var value = 1,
        minValue = 0,
        maxValue = 30;

    let productMinusBtn = document.querySelectorAll(".product-quantity-minus")
    let productPlusBtn = document.querySelectorAll(".product-quantity-plus")
    productMinusBtn.forEach((element) => {
        element.onclick = () => {
            value = Number(element.parentElement.childNodes[3].value)
            if (value > minValue) {
                value = Number(element.parentElement.childNodes[3].value) - 1;
                element.parentElement.childNodes[3].value = value;
            }
        }
    })
    productPlusBtn.forEach((element) => {
        element.onclick = () => {
            if (value < maxValue) {
                value = Number(element.parentElement.childNodes[3].value) + 1;
                element.parentElement.childNodes[3].value = value;
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
            
            console.log("dfkshdfiuh");
            document.querySelectorAll(".tooltip").forEach((ele)=>{
                ele.remove()
            })
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