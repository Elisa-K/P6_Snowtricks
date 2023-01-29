const addFormDeleteLink = (item) => {
	const removeFormButton = document.createElement('div');
	removeFormButton.classList.add('me-3');
	removeFormButton.insertAdjacentHTML('afterbegin','<i class="bi bi-trash3-fill"></i>');
	item.prepend(removeFormButton);
	removeFormButton.addEventListener('click', (e) => {
		e.preventDefault();
		item.remove();
	})
}

const addFormToCollection = (e) => {
	const collectionHolder = document.querySelector('.' + e.currentTarget.dataset.listSelector);
	const item = document.createElement('li');
	item.innerHTML = collectionHolder.dataset.prototype.replace(/__name__/g, collectionHolder.dataset.index);
	collectionHolder.appendChild(item);
	collectionHolder.dataset.index++;

	addFormDeleteLink(item);

}

const addPreviewImg = (e) => {
	if (e.target.files.length > 0) {
		document.getElementById('img-preview-featured').src = URL.createObjectURL(e.target.files[0]);
	} else {
		document.getElementById('img-preview-featured').src = "";
	}

}

document.querySelectorAll('.add-photo').forEach(btn => {
	btn.addEventListener('click', addFormToCollection)
});

document.querySelectorAll('.add-video').forEach(btn => {
	btn.addEventListener('click', addFormToCollection)
});


let inputFeatured = document.getElementById('trick_form_featuredImage');

inputFeatured.addEventListener('change', addPreviewImg)