const signInBtnLink = document.querySelector('.signInBtn-link');
const signUpBtnLink = document.querySelector('.signUpBtn-link');
const wrapper = document.querySelector('.wrapper');
signUpBtnLink.addEventListener('click', () => {
    wrapper.classList.toggle('active');
});
signInBtnLink.addEventListener('click', () => {
    wrapper.classList.toggle('active');
});

document.addEventListener('DOMContentLoaded', function () {
    // Get all sidebar links
    const sidebarLinks = document.querySelectorAll('.side-menu a');
    const contentSections = document.querySelectorAll('.content-section');

    sidebarLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault(); // Prevent page reload

            // Remove "active" class from all links
            sidebarLinks.forEach(link => link.parentElement.classList.remove('active'));

            // Add "active" class to the clicked link
            this.parentElement.classList.add('active');

            // Hide all content sections
            contentSections.forEach(section => section.classList.remove('active'));

            // Show the selected content section
            const contentId = this.getAttribute('data-content');
            document.getElementById(contentId).classList.add('active');
        });
    });
});