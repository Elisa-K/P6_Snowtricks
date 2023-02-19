let btnMoreComments = document.getElementById('btnMoreComments');
let moreCommentsHTML = document.getElementById('moreComments');
let spinner = document.getElementById('spinner');

function showSpinner() {
	spinner.classList.remove('invisible');
}

function hideSpinner() {
	spinner.classList.add('invisible');
}

function loadmore(input) {
	showSpinner();

	let slug = input.dataset.slug;
	let start = parseInt(input.dataset.start);
	let limit = parseInt(input.dataset.limit);

	const params = {
		'start': start,
		'limit': limit
	};
	const options = {
		method: 'POST',
		body: JSON.stringify(params)
	};

	fetch('/loadmorecomments/'+ slug, options)
		.then(response => response.json())
		.then(data => {
			hideSpinner();
			data.html.forEach((item) => {
				moreCommentsHTML.innerHTML += item;
			})
				start += limit;
				input.dataset.start = start;

			if (data.lastResult) {
				input.classList.add('d-none');
			}
		});

}

loadmore(btnMoreComments);
btnMoreComments.addEventListener('click', function (e) {
	e.preventDefault();
	loadmore(this);
})