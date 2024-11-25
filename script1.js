const allSideMenu = document.querySelectorAll('#sidebar .side-menu.top li a');

allSideMenu.forEach(item=> {
	const li = item.parentElement;

	item.addEventListener('click', function () {
		allSideMenu.forEach(i=> {
			i.parentElement.classList.remove('active');
		})
		li.classList.add('active');
	})
});




// TOGGLE SIDEBAR
const menuBar = document.querySelector('#content nav .bx.bx-menu');
const sidebar = document.getElementById('sidebar');

menuBar.addEventListener('click', function () {
	sidebar.classList.toggle('hide');
})







const searchButton = document.querySelector('#content nav form .form-input button');
const searchButtonIcon = document.querySelector('#content nav form .form-input button .bx');
const searchForm = document.querySelector('#content nav form');

searchButton.addEventListener('click', function (e) {
	if(window.innerWidth < 576) {
		e.preventDefault();
		searchForm.classList.toggle('show');
		if(searchForm.classList.contains('show')) {
			searchButtonIcon.classList.replace('bx-search', 'bx-x');
		} else {
			searchButtonIcon.classList.replace('bx-x', 'bx-search');
		}
	}
})





if(window.innerWidth < 768) {
	sidebar.classList.add('hide');
} else if(window.innerWidth > 576) {
	searchButtonIcon.classList.replace('bx-x', 'bx-search');
	searchForm.classList.remove('show');
}


window.addEventListener('resize', function () {
	if(this.innerWidth > 576) {
		searchButtonIcon.classList.replace('bx-x', 'bx-search');
		searchForm.classList.remove('show');
	}
})

const switchMode = document.getElementById('switch-mode');

switchMode.addEventListener('change', function () {
	if(this.checked) {
		document.body.classList.add('dark');
	} else {
		document.body.classList.remove('dark');
	}
})


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



document.addEventListener("DOMContentLoaded", function () {
    // Select all links with 'data-content' and all sections
    const links = document.querySelectorAll("[data-content]");
    const sections = document.querySelectorAll(".content-section");

    links.forEach(link => {
        link.addEventListener("click", (event) => {
            event.preventDefault();

            // Get the target section ID from data-content
            const targetId = link.getAttribute("data-content");

            // Hide all sections
            sections.forEach(section => section.classList.remove("active"));

            // Show the target section
            const targetSection = document.getElementById(targetId);
            if (targetSection) {
                targetSection.classList.add("active");
                targetSection.scrollIntoView({ behavior: "smooth" });
            }
        });
    });
});










/*sttus*/


document.addEventListener("DOMContentLoaded", function () {
     const sidebarItems = document.querySelectorAll(".side-menu li");
    const activeItemKey = "activeSidebarItem";

        // Set the active menu item on page load
    const activeItem = localStorage.getItem(activeItemKey);
    if (activeItem) {
        sidebarItems.forEach(item => {
            item.classList.remove("active"); // Remove previous active state
            if (item.dataset.content === activeItem) {
                item.classList.add("active"); // Add active state to saved item
            }
        });
    }

        // Update active menu item on click
    sidebarItems.forEach(item => {
        item.addEventListener("click", function () {
            localStorage.setItem(activeItemKey, this.dataset.content); // Save active item to local storage
            sidebarItems.forEach(el => el.classList.remove("active")); // Remove previous active state
            this.classList.add("active"); // Set clicked item as active
         });
    });
});




document.addEventListener("DOMContentLoaded", () => {
    // Get the server response elements
    const errorMessage = document.getElementById("error-message").textContent.trim();
    const successMessage = document.getElementById("success-message").textContent.trim();
    const bonusMessage = document.getElementById("bonus-message");
    const claimButton = document.getElementById("claim-bonus-btn");

    // Check if there's an error message
    if (errorMessage) {
        bonusMessage.textContent = errorMessage;
        bonusMessage.style.color = "red";
        bonusMessage.classList.remove("hidden");
    }

    // Check if there's a success message
    if (successMessage) {
        bonusMessage.textContent = successMessage;
        bonusMessage.style.color = "green";
        bonusMessage.classList.remove("hidden");

        // Optionally, disable the claim button after claiming the bonus
        claimButton.style.display = "none"; // Hides the button
    }
});





document.getElementById("claim-bonus-form").addEventListener("submit", function (event) {
    event.preventDefault(); // Prevent full page reload

    const formData = new FormData(this);

    fetch("claim_bonus.php", {
        method: "POST",
        body: formData,
    })
        .then(response => response.json())
        .then(data => {
            const messageElement = document.createElement("p");
            messageElement.style.color = data.success ? "green" : "red";
            messageElement.textContent = data.message;

            const rewardsSection = document.getElementById("REWARDS");
            rewardsSection.appendChild(messageElement);
        })
        .catch(error => console.error("Error:", error));
});


document.addEventListener('DOMContentLoaded', () => {
    const flashMessages = document.querySelectorAll('.flash-message');
    flashMessages.forEach(message => {
        setTimeout(() => {
            message.style.display = 'none';
        }, 5000); // Hide after 5 seconds
    });
});


const weightedPrizes = [
    { prize: "KSH.100", weight: 0 },
    { prize: "KSH.10", weight: 2 },
    { prize: "KSH.5", weight: 2 },
    { prize: "KSH.0", weight: 5 },
    { prize: "KSH.5", weight: 1 },
    { prize: "KSH.0", weight: 5 },
    { prize: "KSH.110", weight: 0 },
    { prize: "KSH.21", weight: 1 },
  ];
  
  // Function to select a random prize based on weights
  function getRandomWeightedPrize(weightedPrizes) {
    const totalWeight = weightedPrizes.reduce((sum, item) => sum + item.weight, 0);
    const randomValue = Math.random() * totalWeight;
    let cumulativeWeight = 0;
  
    for (let item of weightedPrizes) {
      cumulativeWeight += item.weight;
      if (randomValue < cumulativeWeight) {
        return item.prize;
      }
    }
  }
  
  // Wheel setup
  const wheel = document.getElementById("wheel");
  const spinButton = document.getElementById("spinButton");
  const resultDisplay = document.getElementById("result");
  const segmentCount = weightedPrizes.length;
  
  // Calculate each segment's angle
  const segmentAngle = 360 / segmentCount;
  
  // Populate the wheel with prizes
  function drawWheel() {
    const ctx = wheel.getContext("2d");
    const radius = wheel.width / 2;
    for (let i = 0; i < segmentCount; i++) {
      const startAngle = (i * segmentAngle * Math.PI) / 180;
      const endAngle = ((i + 1) * segmentAngle * Math.PI) / 180;
  
      // Set random colors for each segment
      ctx.fillStyle = `hsl(${(i * 45) % 360}, 70%, 60%)`;
      ctx.beginPath();
      ctx.moveTo(radius, radius);
      ctx.arc(radius, radius, radius, startAngle, endAngle);
      ctx.closePath();
      ctx.fill();
  
      // Add prize text
      ctx.save();
      ctx.translate(radius, radius);
      ctx.rotate(startAngle + segmentAngle / 2);
      ctx.textAlign = "right";
      ctx.fillStyle = "white";
      ctx.font = "14px Arial";
      ctx.fillText(
        weightedPrizes[i].prize,
        radius - 10,
        5 // Adjust position
      );
      ctx.restore();
    }
  }
  
  // Spin the wheel
  let spinning = false;
  
  spinButton.addEventListener("click", function () {
    if (spinning) return;
    spinning = true;
  
    // Get the selected prize
    const winningPrize = getRandomWeightedPrize(weightedPrizes);
  
    // Determine the angle to stop at
    const winningIndex = weightedPrizes.findIndex((p) => p.prize === winningPrize);
    const winningAngle = 360 - (winningIndex * segmentAngle + segmentAngle / 2);
  
    // Randomize spin duration and speed
    const spinDuration = 4000; // 4 seconds
    const spinExtra = Math.floor(Math.random() * 360); // Extra rotation
  
    const finalRotation = 360 * 5 + spinExtra + winningAngle; // 5 full spins + random + target
  
    // Animate the spin
    wheel.style.transition = `transform ${spinDuration / 1000}s ease-out`;
    wheel.style.transform = `rotate(${finalRotation}deg)`;
  
    // Reset the wheel after spin
    setTimeout(() => {
      wheel.style.transition = "none";
      wheel.style.transform = `rotate(${winningAngle}deg)`; // Reset to final position
      resultDisplay.textContent = `You won: ${winningPrize}`;
      spinning = false;
    }, spinDuration);
  });
  
  // Draw the initial wheel
  drawWheel();
  