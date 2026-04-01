(function () {
    "use strict";
  
    let checkAll = document.querySelector('.check-all');
    checkAll.addEventListener('click', checkAllFn)
  
    function checkAllFn() {
      if (checkAll.checked) {
        document.querySelectorAll('.mail-checkbox input').forEach(function (e) {
          e.closest('.mail-list').classList.add('selected');
          e.checked = true;
        });
      }
      else {
        document.querySelectorAll('.mail-checkbox input').forEach(function (e) {
          e.closest('.mail-list').classList.remove('selected');
          e.checked = false;
        });
      }
    };
    // mail//
    let mailInbox = document.querySelector(".main-inbox");
    let mailList = document.querySelectorAll(".mail-list");
    let mailView = document.querySelector(".mail-view");
    let mailBtn = document.querySelectorAll(".mail-btn");
  
    mailList.forEach((ele) => {
      ele.addEventListener("click", () => {
        mailInbox.classList.add("d-none")
        mailView.classList.remove("d-none")
      })
    })
    
    mailBtn.forEach((e) => {
      e.addEventListener("click", () => {
        mailInbox.classList.remove("d-none")
        mailView.classList.add("d-none")
      })
    })
  
    // mailBtn.addEventListener("click", () => {
    //   mailInbox.classList.remove("d-none")
    //   mailView.classList.add("d-none")
    // })
    // mail//
  
  
    

    //modal //
    let maxScreen = document.querySelector('.ti-modal-max-screen');
    let minScreen = document.querySelector('.ti-modal-min-screen');
    // let closeBtn = document.querySelector('.ti-modal-close-btn');
  
    maxScreen.addEventListener("click", () => {
      document.querySelector(".modal-dialog").classList.toggle("modal-fullscreen");
      document.querySelector(".modal-dialog").classList.remove("modal-md");
    })
  
    minScreen.addEventListener("click", () => {
      document.querySelector(".modal-dialog").classList.toggle("modal-md");
      document.querySelector(".modal-dialog").classList.remove("modal-fullscreen");
    })
    // closeBtn.addEventListener("click", () => {
    //   document.querySelector(".modal").classList.remove("modal-md");
    //   document.querySelector(".modal").classList.remove("modal-fullscreen");
    // })
    //modal//
  
    // mail//
    let ccBtn = document.querySelector('.btn-cc');
    let ccBtn2 = document.querySelector('.btn-bcc-cc');
    let bccBtn = document.querySelector('.btn-bcc');
    let bccBtn2 = document.querySelector('.btn-cc-bcc');
    let mailCc = document.querySelector('.mail-cc');
    let mailBcc = document.querySelector('.mail-bcc');
    let MailMain = document.querySelector('#mail-compose');
  
    ccBtn.addEventListener("click", () => {
      MailMain.classList.add("mail-add-cc");
      mailCc.classList.remove("d-none");
    })
    ccBtn2.addEventListener("click", () => {
      MailMain.classList.add("mail-add-cc2");
      mailCc.classList.remove("d-none");
    })
  
    bccBtn2.addEventListener("click", () => {
      MailMain.classList.add("mail-add-bcc");
      mailBcc.classList.remove("d-none");
    })
  
    bccBtn.addEventListener("click", () => {
      MailMain.classList.add("mail-add-bcc2");
      mailBcc.classList.remove("d-none");
    })
  
    // mail//
  
  
  })();
  
  let changeTheInfo = (img,name,mail,subject,date) => {
    document.querySelector(".mail-img").src = `http://127.0.0.1:8000/build/assets/img/users/${img}.jpg`;
    document.querySelector(".mail-name").innerText = name;
    document.querySelector(".mail-email").innerText = mail;
    document.querySelector(".mail-subject").innerText = subject;
    document.querySelector(".mail-date").innerText = date;
  }