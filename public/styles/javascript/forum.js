const modal = document.getElementById("modal");
    const createSubjectButton = document.getElementById("create-subject");
    const closeModalButton = document.querySelector(".close");
    
    createSubjectButton.addEventListener("click", () => {
        modal.style.display = "block";
    });

    closeModalButton.addEventListener("click", () => {
        modal.style.display = "none";
    });

    window.addEventListener("click", (event) => {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    });

  document.querySelectorAll('.premier-bloc-forum ul li a').forEach(item => {
    item.addEventListener('click', () => {
      document.querySelectorAll('.premier-bloc-forum ul li a')
        .forEach(el => el.classList.remove('active'));
      item.classList.add('active');
    });
  });