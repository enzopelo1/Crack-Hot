function toggleContent() {
    const hiddenContent = document.querySelector('.hidden-content');
    const toggleArrow = document.querySelector('.toggle-arrow');
    if (hiddenContent.style.display === 'none' || hiddenContent.style.display === '') {
        hiddenContent.style.display = 'block';
        toggleArrow.classList.add('rotate'); // Ajoute la rotation
    } else {
        hiddenContent.style.display = 'none';
        toggleArrow.classList.remove('rotate'); // Enlève la rotation
    }
}



function toggleContent(id) {
    var content = document.getElementById('content-' + id);
    if (content.style.display === "none" || content.style.display === "") {
        content.style.display = "block";
    } else {
        content.style.display = "none";
    }
}
