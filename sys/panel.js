const buttonHome = document.getElementById('home_button')
const buttonMenu = document.getElementById('menu_button')
const buttonImage = document.getElementById('image_manager')

const home = document.getElementById('home')
const menu = document.getElementById('food')
const image = document.getElementById('image')

// buttonHome.style.backgroundColor = 'green'

const cookieMenu = sessionStorage.getItem('menu')
const cookieImage = sessionStorage.getItem('image')

if (cookieMenu == 'd-block') {
	menu.classList.replace('d-none', 'd-flex')
	image.classList.replace('d-flex', 'd-none')
	home.classList.replace('d-flex', 'd-none')
}
if (cookieImage == 'd-block') {
	image.classList.replace('d-none', 'd-flex')
	home.classList.replace('d-flex', 'd-none')
	menu.classList.replace('d-flex', 'd-none')
}

buttonImage.addEventListener('click', function () {
	image.classList.replace('d-none', 'd-flex')
	home.classList.replace('d-flex', 'd-none')
	menu.classList.replace('d-flex', 'd-none')

	sessionStorage.setItem('image', 'd-block')
	sessionStorage.removeItem('menu', 'd-block')
})

buttonHome.addEventListener('click', function () {
	home.classList.replace('d-none', 'd-flex')
	menu.classList.replace('d-flex', 'd-none')
	image.classList.replace('d-flex', 'd-none')

	sessionStorage.removeItem('menu', 'd-block')
	sessionStorage.removeItem('image', 'd-block')
})

buttonMenu.addEventListener('click', function () {
	menu.classList.replace('d-none', 'd-flex')
	home.classList.replace('d-flex', 'd-none')
	image.classList.replace('d-flex', 'd-none')

	sessionStorage.setItem('menu', 'd-block')
	sessionStorage.removeItem('image', 'd-block')
})
