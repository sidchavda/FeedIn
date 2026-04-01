(function () {
    "use strict"

    // for color selection
    const multipleCancelButton = new Choices(
        '#product-color-add',
        {
            allowHTML: true,
            removeItemButton: true,
        }
    );

    // for product tags
    const multipleCancelButton1 = new Choices(
        '#product-tags',
        {
            allowHTML: true,
            removeItemButton: true,
        }
    );

    // for product features
    var toolbarOptions = [
        [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
        [{ 'font': [] }],
        ['bold', 'italic', 'underline', 'strike'],        // toggled buttons
        ['blockquote', 'code-block'],

        [{ 'header': 1 }, { 'header': 2 }],               // custom button values
        [{ 'list': 'ordered' }, { 'list': 'bullet' }],

        [{ 'color': [] }, { 'background': [] }],          // dropdown with defaults from theme
        [{ 'align': [] }],
        ['clean']                                         // remove formatting button
    ];
    var quill = new Quill('#product-features', {
        modules: {
            toolbar: toolbarOptions
        },
        theme: 'snow'
    });

     /* To choose date and time */
     flatpickr("#datetime", {
        enableTime: true,
        dateFormat: "Y-m-d H:i",
    });
    
    /* For DateTimePicker with Limited Time Range */
    flatpickr("#limitdatetime", {
        enableTime: true,
        minTime: "16:00",
        maxTime: "22:00"
    });

    // for product images upload
    const MultipleElement1 = document.querySelector('.product-Images');
    FilePond.create(MultipleElement1,);

    // for documents upload
    const MultipleElement = document.querySelector('.product-documents');
    FilePond.create(MultipleElement,);


})();