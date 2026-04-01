(function () {
    "use strict"

    dragula([document.querySelector('#new-tasks-draggable'), document.querySelector('#todo-tasks-draggable'), document.querySelector('#inprogress-tasks-draggable'),document.querySelector('#completed-tasks-draggable')]);


    /* multi select with remove button */
    const multipleCancelButton = new Choices(
        '#choices-multiple-remove-button1',
        {
            allowHTML: true,
            removeItemButton: true,
        }
    );
    const multipleCancelButton1 = new Choices(
        '#choices-multiple-remove-button2',
        {
            allowHTML: true,
            removeItemButton: true,
        }
    );

    /* TargetDate Picker */
    flatpickr("#targetDate", {
        enableTime: true,
        dateFormat: "Y-m-d H:i",
    });
    /* TargetDate Picker */
    flatpickr("#targetDate1", {
        enableTime: true,
        dateFormat: "Y-m-d H:i",
    });

     /* filepond */
     FilePond.registerPlugin(
        FilePondPluginImagePreview,
        FilePondPluginImageExifOrientation,
        FilePondPluginFileValidateSize,
        FilePondPluginFileEncode,
        FilePondPluginImageEdit,
        FilePondPluginFileValidateType,
        FilePondPluginImageCrop,
        FilePondPluginImageResize,
        FilePondPluginImageTransform
    );

    /* multiple upload */
    const MultipleElement = document.querySelector('.multiple-filepond');
    FilePond.create(MultipleElement,);

})();