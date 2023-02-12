let avatar = document.getElementById('avatarImg').src;

const addPreviewAvatar = (e) => {
	if (e.target.files.length > 0) {
		document.getElementById('avatarImg').src = URL.createObjectURL(e.target.files[0]);
	} else {
		document.getElementById('avatarImg').src = avatar;
	}
}

let inputAvatar = document.getElementById('avatar_form_avatarPath');

inputAvatar.addEventListener('change', addPreviewAvatar);

