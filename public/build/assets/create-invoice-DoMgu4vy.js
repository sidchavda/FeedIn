(function(){flatpickr("#invoice-date-issued",{}),flatpickr("#invoice-date-due",{}),document.querySelector(".invoice-add-item").onclick=()=>{let e=`<tr class="invoice-list"> 
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
    </tr> `;document.querySelector(".invoice-body").innerHTML+=e};var o=document.querySelectorAll(".quantity-minus"),i=document.querySelectorAll(".quantity-plus"),l=0,r=30;o.forEach(e=>{e.onclick=function(n){let t=n.currentTarget.nextElementSibling.value;t>l&&(t=t-1,n.currentTarget.nextElementSibling.value=t)}}),i.forEach(e=>{e.onclick=function(n){let t=n.currentTarget.previousElementSibling.value;t<r&&(t=Number(t)+1,n.currentTarget.previousElementSibling.value=t)}}),document.addEventListener("DOMContentLoaded",function(){var e=document.querySelectorAll("[data-trigger]");for(let t=0;t<e.length;++t){var n=e[t];new Choices(n,{allowHTML:!0,searchEnabled:!1,removeItemButton:!0})}}),document.querySelectorAll(".invoice-btn").forEach(e=>{e.onclick=()=>{e.closest(".invoice-list").remove()}}),document.getElementById("invoice-create").onclick=()=>{document.querySelector(".invoice-title").innerHTML="Create Invoice"}})();
