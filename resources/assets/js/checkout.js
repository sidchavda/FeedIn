
  /* options added via config with no search */
  var singleNoSearch = new Choices('#choices-single-no-search', {
    allowHTML: true,
    searchEnabled: false,
    removeItemButton: true,
    choices: [
      { value: 'Home', label: 'Home' },
      { value: 'Office', label: 'Office' },
      { value: 'Select One', label: 'Select One' },
    ],
  })
  var singleNoSearch = new Choices('#choices-single-no-search2', {
    allowHTML: true,
    searchEnabled: false,
    removeItemButton: true,
    choices: [
      { value: '7AM- 9PM', label: '7AM- 9PM' },
      { value: '9AM-7PM', label: '9AM-7PM' },
      { value: 'Select One', label: 'Select One' },
    ],
  })