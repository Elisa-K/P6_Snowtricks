const addFormToCollection = (e) => {
	const collectionHolder = document.querySelector(e.currentTarget.dataset.listSelector);
	const prototype = collectionHolder.dataset.prototype;

	const item = document.createElement("div");
	item.classList.add("col-12", "col-lg-4", "text-center");
	item.innerHTML += prototype.replace(/__name__/g, collectionHolder.dataset.index);
	collectionHolder.appendChild(item);
	collectionHolder.dataset.index++;

	item.querySelector('.btn-remove').addEventListener('click', (e) => {
		item.remove();
	});

	item.querySelector('.input-photo').addEventListener('change', (e) => {
		if (e.target.files.length > 0) {
			item.querySelector('.img-preview').src = URL.createObjectURL(e.target.files[0]);
		} else {
			document.getElementById('img-preview').src = "";
		}
	});

}

const addPreviewImg = (e) => {

	if (e.target.files.length > 0) {
		document.getElementById('img-preview-featured').src = URL.createObjectURL(e.target.files[0]);
	} else {
		document.getElementById('img-preview-featured').src = "";
	}
}

document.querySelectorAll('.add-media').forEach(btn => {
	btn.addEventListener('click', addFormToCollection)
});

document.querySelectorAll('.btn-remove').forEach(btn => {
	btn.addEventListener('click', (e) => {
		console.log(btn.parentNode.parentNode.remove());
	});
});

let inputFeatured = document.getElementById('trick_form_featuredImage');

inputFeatured.addEventListener('change', addPreviewImg);