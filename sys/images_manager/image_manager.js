const input = document.getElementById('image_uploads')
const preview = document.getElementById('preview')

input.style.opacity = 0

const fileTypes = [
	'image/apng',
	'image/bmp',
	'image/gif',
	'image/jpeg',
	'image/pjpeg',
	'image/png',
	'image/svg+xml',
	'image/tiff',
	'image/webp',
	'image/x-icon',
]

function validFileType(file) {
	return fileTypes.includes(file.type)
}

function returnFileSize(number) {
	if (number < 1024) {
		return `${number} bytes`
	} else if (number >= 1024 && number < 1048576) {
		return `${(number / 1024).toFixed(1)} KB`
	} else if (number >= 1048576) {
		return `${(number / 1048576).toFixed(1)} MB`
	}
}

function updateImageDisplay() {
	while (preview.firstChild) {
		preview.removeChild(preview.firstChild)
	}

	const curFiles = input.files
	if (curFiles.length === 0) {
		const para = document.createElement('p')
		para.textContent = 'Aucun fichier selectionné'
		preview.appendChild(para)
	} else {
		const list = document.createElement('div')
		list.className =
			'd-inline-flex  gap-4 flex-wrap flex-column flex-md-row justify-content-center align-items-center px-4'
		// preview.appendChild(list)

		for (const file of curFiles) {
			const listItem = document.createElement('li')
			listItem.className = 'd-flex align-content-center'
			listItem.style = 'list-style-type: none;'
			const para = document.createElement('p')
			if (validFileType(file)) {
				// para.textContent = `Taille du fichier ${returnFileSize(file.size)}.`
				const image = document.createElement('img')
				image.className = 'd-block'
				image.src = URL.createObjectURL(file)
				// image.style.height = '50%'
				image.style.width = '15rem'
				// console.log(image)

				listItem.appendChild(image)
				// listItem.appendChild(para)
			} else {
				para.textContent = `Le nom du fichier ${file.name}: N'a pas un type valide. Update your selection.`
				listItem.appendChild(para)
			}
			preview.appendChild(listItem)
			// list.appendChild(listItem)
		}
	}
}
input.addEventListener('change', updateImageDisplay)
