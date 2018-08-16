function resetDeleteModal(){
	$("#deleteModal").html(
		'<div class="modal-dialog" role="document">' +
		'<div class="modal-content">' +
		'<div class="modal-body">' +
		'<div class="text-center">' +
		'<i class="fas fa-spinner fa-pulse"></i>' +
		'</div>' +
		'</div>' +
		'</div>' +
		'</div>'
	);
}

function isDeleteModalOpen(){
	return $("#deleteModal").hasClass('show');
}

function closeDeleteModal(){
	$("#deleteModal").modal('hide');
}

function showDeleteModal(postId){
	resetDeleteModal();
	
	let deleteModal = $("#deleteModal");
	
	$.ajax({
		url: "/scripts/postInfo",
		data: {
			csrf_token: CSRF_TOKEN,
			postId: postId
		},
		method: "POST",
		
		success: function(json){
			if(json.hasOwnProperty("id")){
				let user = json.user;
				let content = "";
				
				content = content.concat('<div class="mb-4">');
				
				content = content.concat(json.followButton);
				
				content = content.concat(
					'<div class="float-left mr-2">' +
					'<a href="/' + user.username + '" class="clearUnderline">' +
					'<img width="48" height="48" src="' + user.avatar + '" class="rounded"/>' +
					'</a>' +
					'</div>'
				);
				
				content = content.concat('<div class="ml-2">');
				
				content = content.concat(
					'<div><a href="/' + user.username + '" class="clearUnderline font-weight-bold mb-0" style="font-size:20px">' +
					user.displayName +
					'</a></div>'
				);
				
				content = content.concat(
					'<div class="text-muted" style="margin-top: -6px">' +
					'@' + user.username +
					'</div>'
				);
				
				content = content.concat('</div>');
				
				content = content.concat('</div>');
				
				let c = json.hasOwnProperty("parent") && json.parent != null ? '<div class="small text-muted">Replying to <a href="/' + json.parent.user.username + '" class="clearUnderline">@' + json.parent.user.username + '</a></div>' : "";
				
				content = content.concat(
					'<div class="mt-2">' +
					c + 
					'<p style="font-size: 27px;">' +
					twemoji.parse(json.text) +
					'</p>' +
					'<p class="small text-muted"><i class="far fa-clock"></i> Posted ' +
					json.time +
					'</p>' +
					'</div>'
				);
				
				deleteModal.html(
					'<div class="modal-dialog" role="document">' +
					'<div class="modal-content">' +
					'<div class="modal-header">' +
					'<h5 class="modal-title">Are you sure you want to delete this post?</h5>' +
					'</div>' +
					
					'<div class="modal-body">' +
					content +
					'</div>' +
					
					'<div class="modal-footer">' +
					'<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>' +
					'<button type="button" class="finDel btn btn-danger" data-post-id="' + postId + '">Delete</button>' +
					'</div>' +
					'</div>' +
					'</div>'
				);
				
				loadBasic();
				
				if(!isDeleteModalOpen())
				deleteModal.modal();
			} else if(json.hasOwnProperty("error")){
				deleteModal.html(
					'<div class="modal-dialog" role="document">' +
					'<div class="modal-content">' +
					'<div class="modal-body">' +
					json.error +
					'</div>' +
					'</div>' +
					'</div>'
				);
				
				if(!isDeleteModalOpen())
				deleteModal.modal();
			} else {
				deleteModal.html(
					'<div class="modal-dialog" role="document">' +
					'<div class="modal-content">' +
					'<div class="modal-body">' +
					json +
					'</div>' +
					'</div>' +
					'</div>'
				);
				
				if(!isDeleteModalOpen())
				deleteModal.modal();
			}
		},
		
		error: function(xhr,status,error){
			console.log(xhr);
			console.log(status);
			console.log(error);
		}
	});
}

function resetStatusModal(){
	$("#statusModal").html(
		'<div class="modal-dialog" role="document">' +
		'<div class="modal-content">' +
		'<div class="modal-body">' +
		'<div class="text-center">' +
		'<i class="fas fa-spinner fa-pulse"></i>' +
		'</div>' +
		'</div>' +
		'</div>' +
		'</div>'
	);
}

function isStatusModalOpen(){
	return $("#statusModal").hasClass('show');
}

function closeStatusModal(){
	$("#statusModal").modal('hide');
}

function showStatusModal(postId){
	resetStatusModal();
	
	let statusModal = $("#statusModal");
	
	if(restoreUrl == null || restoreUrl == "") restoreUrl = window.location.pathname;
	if(restoreTitle == null || restoreTitle == "") restoreTitle = $(document).find("title").text();
	
	$.ajax({
		url: "/scripts/postInfo",
		data: {
			csrf_token: CSRF_TOKEN,
			postId: postId
		},
		method: "POST",
		
		success: function(json){
			if(json.hasOwnProperty("id")){
				CURRENT_STATUS_MODAL = json.id;
				let user = json.user;
				let content = "";
				
				let replies = json.replies;
				
				let d = json;
				let hasParent = false;
				
				while(d.hasOwnProperty("parent") && d.parent != null){
					d = d.parent;
					hasParent = true;
					
					content = content.concat(d.listHtml);
					/*'<div class="card feedEntry my-2 statusTrigger" data-status-render="' + d.id + '" data-entry-id="' + d.id + '">' +
					'<div class="py-1 px-3">' +
					'<div class="row">' +
					'<div class="float-left">' +
					'<a href="/' + d.user.username + '" class="clearUnderline ignoreParentClick">' +
					'<img class="rounded mx-1 my-1" src="' + d.user.avatar + '" width="36" height="36"/>' +
					'</a>' +
					'</div>' +
					
					'<div class="float-left ml-1" style="max-width: 414px;">' +
					'<p class="mb-0 small">' +
					'<a href="/' + d.user.username + '" class="clearUnderline ignoreParentClick">' +
					'<span class="font-weight-bold">' + d.user.displayName + '</span>' +
					'</a>' +
					
					' <span class="text-muted font-weight-normal">@' + d.user.username + '</span> ' +
					
					'&bull; ' +
					
					d.time +
					'</p>' +
					
					'<p class="mb-0">' +
					twemoji.parse(d.text) +
					'</p>' +
					
					d.postActionButtons +
					'</div>' +
					'</div>' +
					'</div>' +
					'</div>'
					.concat(content);*/
				}
				
				if(hasParent == true)
				content = content.concat("<hr/>");
				
				content = content.concat('<div class="mb-4">');
				
				content = content.concat(json.followButton);
				
				content = content.concat(
					'<div class="float-left mr-2">' +
					'<a href="/' + user.username + '" class="clearUnderline">' +
					'<img width="48" height="48" src="' + user.avatar + '" class="rounded"/>' +
					'</a>' +
					'</div>'
				);
				
				content = content.concat('<div class="ml-2">');
				
				content = content.concat(
					'<div><a href="/' + user.username + '" class="clearUnderline font-weight-bold mb-0" style="font-size:20px">' +
					user.displayName +
					'</a></div>'
				);
				
				content = content.concat(
					'<div class="text-muted" style="margin-top: -6px">' +
					'@' + user.username +
					'</div>'
				);
				
				content = content.concat('</div>');
				
				content = content.concat('</div>');
				
				let c = json.hasOwnProperty("parent") && json.parent != null ? '<div class="small text-muted">Replying to <a href="/' + json.parent.user.username + '" class="clearUnderline">@' + json.parent.user.username + '</a></div>' : "";
				
				content = content.concat(
					'<div class="mt-2">' +
					c + 
					'<p style="font-size: 27px;">' +
					twemoji.parse(json.text) +
					'</p>' +
					'<p class="small text-muted"><i class="far fa-clock"></i> Posted ' +
					json.time +
					'</p>' +
					'</div>'
				);
				
				content = content.concat('<hr/>');
				
				content = content.concat(json.postActionButtons);
				
				if(typeof CURRENT_USER !== "undefined" && json.hasOwnProperty("postForm")){
					content = content.concat(json.postForm);
				}
				
				content = content.concat('<div class="replies">');
				
				if(replies.length > 0){
					replies.forEach(reply => {
						content = content.concat(reply.listHtml);
						/*content = content.concat(
							'<div class="card feedEntry my-2 statusTrigger" data-status-render="' + reply.id + '" data-entry-id="' + reply.id + '">' +
							'<div class="py-1 px-3">' +
							'<div class="row">' +
							'<div class="float-left">' +
							'<a href="/' + reply.user.username + '" class="clearUnderline ignoreParentClick">' +
							'<img class="rounded mx-1 my-1" src="' + reply.user.avatar + '" width="36" height="36"/>' +
							'</a>' +
							'</div>' +
							
							'<div class="float-left ml-1" style="max-width: 414px;">' +
							'<p class="mb-0 small">' +
							'<a href="/' + reply.user.username + '" class="clearUnderline ignoreParentClick">' +
							'<span class="font-weight-bold">' + reply.user.displayName + '</span>' +
							'</a>' +
							
							' <span class="text-muted font-weight-normal">@' + reply.user.username + '</span> ' +
							
							'&bull; ' +
							
							reply.time +
							'</p>' +
							
							'<p class="mb-0">' +
							twemoji.parse(reply.text) +
							'</p>' +
							
							reply.postActionButtons +
							'</div>' +
							'</div>' +
							'</div>' +
							'</div>'
						);*/
					});
				}
				
				content = content.concat('</div>');
				
				statusModal.html(
					'<div class="modal-dialog" role="document">' +
					'<div class="modal-content">' +
					'<div class="modal-body">' +
					content +
					'</div>' +
					'</div>' +
					'</div>'
				);
				
				loadBasic();
				
				let title = user.displayName + " on qpost: \"" + limitString(json.textUnfiltered,34,true) + "\"";
				
				history.pushState({postId: postId},title,"/status/" + postId);
				document.title = title; 
				
				if(!isStatusModalOpen())
				statusModal.modal();
			} else if(json.hasOwnProperty("error")){
				statusModal.html(
					'<div class="modal-dialog" role="document">' +
					'<div class="modal-content">' +
					'<div class="modal-body">' +
					json.error +
					'</div>' +
					'</div>' +
					'</div>'
				);
				
				if(!isStatusModalOpen())
				statusModal.modal();
			} else {
				statusModal.html(
					'<div class="modal-dialog" role="document">' +
					'<div class="modal-content">' +
					'<div class="modal-body">' +
					json +
					'</div>' +
					'</div>' +
					'</div>'
				);
				
				if(!isStatusModalOpen())
				statusModal.modal();
			}
		},
		
		error: function(xhr,status,error){
			console.log(xhr);
			console.log(status);
			console.log(error);
		}
	});
}

function limitString(string,length,addDots = true){
	if(addDots){
		length = length-3;
		if(length < 1) length = 1;
	}
	
	if(string.length > length){
		return string.substr(0,length) + (addDots == true ? "..." : "");
	} else {
		return string;
	}
}

function loadHomeFeed(){
	if(typeof HOME_FEED_LAST_POST == "undefined"){
		setTimeout(loadHomeFeed,5000);
		return;
	}
	
	if($("#homePostField").length && $("#homeCharacterCounter").length){
		$.ajax({
			url: "/scripts/extendHomeFeed",
			data: {
				csrf_token: CSRF_TOKEN,
				mode: "loadNew",
				lastPost: HOME_FEED_LAST_POST
			},
			method: "POST",
			
			success: function(result){
				let json = result;
				
				if(json.hasOwnProperty("result")){
					let newHtml = "";
					
					let a = true;
					
					json.result.forEach(post => {
						let postId = post.id;
						
						if(a == true){
							HOME_FEED_LAST_POST = postId;
							a = false;
						}
						
						newHtml = newHtml.concat(post.listHtml);
					});
					
					if($(".feedEntry").length){
						$(".feedContainer").prepend(newHtml);
					} else {
						$(".feedContainer").html(newHtml);
					}
					
					loadBasic();
					
					setTimeout(loadHomeFeed,5000);
				} else {
					console.log(result);
				}
			},
			
			error: function(xhr,status,error){
				console.log(xhr);
				console.log(status);
				console.log(error);
			}
		});
	} else {
		setTimeout(loadHomeFeed,5000);
	}
}

function checkForNotifications(){
	$.ajax({
		url: "/scripts/desktopNotifications",
		method: "GET",
		
		success: function(result){
			let unreadCount = result.unreadCount;
			if(unreadCount > 0){
				$(".notificationTabMainNav").html("notifications <b>(" + unreadCount + ")</b>");
			} else {
				$(".notificationTabMainNav").html("notifications");
			}
			
			if(result.notifications.length > 0){
				result.notifications.forEach(notificationData => {
					let title = null;
					let text = null;
					if(notificationData.type == "NEW_FOLLOWER"){
						title = notificationData.follower.displayName + " is now following you";
						text = notificationData.follower.bio != null ? notificationData.follower.bio : ""
					} else if(notificationData.type == "MENTION"){
						title = notificationData.follower.displayName + " mentioned you";
						text = notificationData.post.textUnfiltered;
					} else if(notificationData.type == "FAVORITE"){
						title = notificationData.follower.displayName + " favorited your post";
						text = notificationData.post.textUnfiltered;
					} else if(notificationData.type == "SHARE"){
						title = notificationData.follower.displayName + " shared your post";
						text = notificationData.post.textUnfiltered;
					} else if(notificationData.type == "REPLY"){
						title = notificationData.follower.displayName + " replied to your post";
						text = notificationData.post.textUnfiltered;
					}
					
					if(title != null && text != null){
						let notification = new Notification(
							title,
							
							{
								body: text.replace("<br/>","\n"),
								icon: notificationData.follower.avatar
							}
						);
						
						notification.onclick = (event) => {
							event.preventDefault();
							window.open("/notifications","_blank");
						};
					}
				});
			}
			
			setTimeout(checkForNotifications,3000);
		},
		
		error: function(xhr,status,error){
			console.log(xhr);
			console.log(status);
			console.log(error);
			
			setTimeout(checkForNotifications,3000);
		}
	});
}

function loadOldHomeFeed(){
	let oldHtml = $(".homeFeedLoadMore").html();
	
	$(".homeFeedLoadMore").html('<i class="fas fa-spinner fa-pulse"></i>');
	
	$.ajax({
		url: "/scripts/extendHomeFeed",
		data: {
			csrf_token: CSRF_TOKEN,
			mode: "loadOld",
			firstPost: HOME_FEED_FIRST_POST
		},
		method: "POST",
		
		success: function(result){
			let json = result;
			
			if(json.hasOwnProperty("result")){
				let newHtml = "";
				
				if(json.result.length > 0){
					let i;
					for(i = 0; i < json.result.length; i++){
						let post = json.result[i];
						
						let postId = post.id;
						
						if(i == json.result.length-1){
							HOME_FEED_FIRST_POST = postId;
						}
						
						newHtml = newHtml.concat(post.listHtml);
					}
					
					if($(".feedEntry").length){
						$(".feedContainer").append(newHtml);
					} else {
						$(".feedContainer").html(newHtml);
					}
					
					$(".homeFeedLoadMore").html(oldHtml);
					
					loadBasic();
				} else {
					$(".homeFeedLoadMore").html('<b>Oops!</b><br/>It seems there is nothing else to load for your home feed.');
				}
			} else {
				console.log(result);
			}
		},
		
		error: function(xhr,status,error){
			console.log(xhr);
			console.log(status);
			console.log(error);
		}
	});
}

function loadBasic(){
	$('[data-toggle="tooltip"]').tooltip({
		trigger: "hover"
	});
	
	$('[data-toggle="popover"]').popover({
		trigger: "focus",
		html: true
	});
	
	$("time.timeago").timeago();
	
	window.addEventListener("load", function () {
		window.cookieconsent.initialise({
			"palette": {
				"popup": {
					"background": "#237afc"
				},
				"button": {
					"background": "#fff",
					"text": "#237afc"
				}
			},
			"content": {
				"href": "https://gigadrivegroup.com/legal/privacy-policy"
			}
		})
	});
	
	$(".datepicker").datepicker();

	$(".convertEmoji").html(function(){
		return twemoji.parse($(this).html());
	}).removeClass("convertEmoji");
}

$(document).ready(function(){
	load();
	loadNotificationAlert();
	resetStatusModal();
	checkForNotifications();
	loadHomeFeed();
	loadDropzone();
	
	if("serviceWorker" in navigator){
		navigator.serviceWorker.register("/serviceWorker.js").then((reg) => {})
		.catch((err) => {
			console.error("Failed to register the service worker",err);
		});
	}
});

function loadNotificationAlert(){
	let pointer = ".notificationPermissionAlert";
	
	if($(pointer).length > 0){
		if(!hasNotificationPermissions() && (!hasCookie("ignoreNotificationAlert") || getCookie("ignoreNotificationAlert") != "true")){
			$(pointer).removeClass("d-none");
		}
	}
}

function hasNotificationPermissions(){
	return Notification.permission === "granted";
}

function loadDropzone(){
	let dz = new Dropzone(document.body, {
		url: "/scripts/mediaUpload",
		paramName: "file",
		maxFilesize: 10,
		maxFiles: 4,
		acceptedFiles: "image/*",
		previewsContainer: ".dropzone-previews",
		clickable: ".addMediaAttachment",
		previewTemplate: $(".preview-template").html()
	});

	dz.on("sending",(file,xhr,formData) => {
		formData.append("csrf_token",CSRF_TOKEN);
	});

	dz.on("addedfile", (file) => {
		let button = Dropzone.createElement("<button class=\"btn btn-danger btn-sm float-right\" style=\"margin-left: -50px; z-index: 99999; position: relative;\"><i class=\"fas fa-trash-alt\"></i></button>");

		button.addEventListener("click", (e) => {
			e.preventDefault();
			e.stopPropagation();

			dz.removeFile(file);
		});

		file.previewElement.prepend(button);
	});
}

function load(){
	Dropzone.autoDiscover = false;

	$(document).on("click",".enableNotifications",function(e){
		e.preventDefault();
		
		$(".notificationPermissionAlert").addClass("d-none");
		Notification.requestPermission();
	});

	$(document).on("click",".addMediaAttachment",function(e){
		e.preventDefault();

		
	});
	
	/*$(document).on("scroll",function(e){
		let scrollValue = $(document).scrollTop();
		
		if(scrollValue >= 10){
			$(".homeFeedSidebar").attr("style","position: fixed; margin-top: -10px");
		} else {
			$(".homeFeedSidebar").removeAttr("style");
		}
	});*/
	
	$(document).on("click",".hideNotifications",function(e){
		e.preventDefault();
		
		$(".notificationPermissionAlert").addClass("d-none");
		setCookie("ignoreNotificationAlert","true",7);
	});
	
	$(document).on("click",".postButton",function(e){
		e.preventDefault();
		
		let postBox = $(this).parent().parent();
		let postField = $(this).parent().find(".postField");
		let postCharacterCounter = $(this).parent().find(".postCharacterCounter");
		
		let isReply = postBox.hasClass("replyForm");
		
		let text = postField.val().trim();
		
		let replyTo = isReply && CURRENT_STATUS_MODAL > 0 ? CURRENT_STATUS_MODAL : null;
		
		if(typeof CSRF_TOKEN !== undefined && typeof POST_CHARACTER_LIMIT !== undefined){
			let token = CSRF_TOKEN;
			let limit = POST_CHARACTER_LIMIT;
			
			let oldHtml = postBox.html();
			
			if(text.length > 0 && text.length <= limit){
				postBox.html('<div class="card-body text-center"><i class="fas fa-spinner fa-pulse"></i></div>');
				
				$.ajax({
					url: "/scripts/createPost",
					data: {
						csrf_token: token,
						text: text,
						replyTo: replyTo
					},
					method: "POST",
					
					success: function(result){
						let json = result;
						
						if(json.hasOwnProperty("post")){
							let post = json.post;
							
							if(post.hasOwnProperty("id") && post.hasOwnProperty("time") && post.hasOwnProperty("text") && post.hasOwnProperty("userName") && post.hasOwnProperty("userDisplayName") && post.hasOwnProperty("userAvatar")){
								let postId = post.id;
								let postTime = post.time;
								let postText = post.text;
								
								let userName = post.userName;
								let userDisplayName = post.userDisplayName;
								let userAvatar = post.userAvatar;
								
								HOME_FEED_LAST_POST = postId;
								
								let postActionButtons = json.postActionButtons;
								
								let newHtml = "";
								
								if(!isReply){
									newHtml = post.listHtml;
									
									if($(".feedEntry").length){
										$(".feedContainer").prepend(newHtml);
									} else {
										$(".feedContainer").html(newHtml);
									}
								} else {
									newHtml =
									'<div class="card feedEntry my-2 statusTrigger" data-status-render="' + postId + '" data-entry-id="' + postId + '">' +
									'<div class="py-1 px-3">' +
									'<div class="row">' +
									'<div class="float-left">' +
									'<a href="/' + userName + '" class="clearUnderline ignoreParentClick">' +
									'<img class="rounded mx-1 my-1" src="' + userAvatar + '" width="36" height="36"/>' +
									'</a>' +
									'</div>' +
									
									'<div class="float-left ml-1" style="max-width: 414px;">' +
									'<p class="mb-0 small">' +
									'<a href="/' + userName + '" class="clearUnderline ignoreParentClick">' +
									'<span class="font-weight-bold">' + userDisplayName + '</span>' +
									'</a>' +
									
									' <span class="text-muted font-weight-normal">@' + userName + '</span> ' +
									
									'&bull; ' +
									
									postTime +
									'</p>' +
									
									'<p class="mb-0">' +
									twemoji.parse(postText) +
									'</p>' +
									
									postActionButtons +
									'</div>' +
									'</div>' +
									'</div>' +
									'</div>';
									
									if($("#statusModal .replies>.card").length){
										$("#statusModal .replies").prepend(newHtml);
									} else {
										$("#statusModal .replies").html(newHtml);
									}
								}
								
								postBox.html(oldHtml);
								postField.val("");
								postCharacterCounter.html(POST_CHARACTER_LIMIT + " characters left");
								loadBasic();
							} else {
								console.log(result);
							}
						} else {
							console.log(result);
						}
					},
					
					error: function(xhr,status,error){
						console.log(xhr);
						console.log(status);
						console.log(error);
					}
				})
			} else {
				console.error("Post text too long or too short!");
			}
		}
	});
	
	$(document).on("click","a.filterLink",function(e){
		e.preventDefault();
		
		if(typeof $(this).attr("href") !== "undefined"){
			let link = $(this).attr("href");
			
			window.location.href = "/out?link=" + encodeURI(link);
		}
	});
	
	$(document).on("click",".statusTrigger",function(e){
		e.preventDefault();
		
		if(typeof $(this).attr("data-status-render") !== "undefined"){
			let postId = $(this).attr("data-status-render");
			
			showStatusModal(postId);
		}
	});
	
	$(document).on("hidden.bs.modal","#statusModal",function(e){
		if(typeof restoreUrl !== "undefined" && typeof restoreTitle !== "undefined"){
			history.pushState("",restoreTitle,restoreUrl);
			document.title = restoreTitle;
			
			restoreUrl = "";
			restoreTitle = "";
			CURRENT_STATUS_MODAL = 0;
		}
	});
	
	$(document).on("click",".replyButton",function(e){
		e.preventDefault();
		
		let postId = $(this).attr("data-reply-id");
		
		if(typeof postId !== "undefined" && postId !== false){
			if(window.location.pathname.endsWith(postId)){
				$("#statusModalPostField").focus();
			} else {
				showStatusModal(postId);
			}
		}
	});
	
	$(document).on("click",".deleteButton",function(e){
		e.preventDefault();
		
		let postId = $(this).attr("data-post-id");
		
		if(typeof postId !== "undefined" && postId !== false){
			if(isStatusModalOpen())
			closeStatusModal();
			
			showDeleteModal(postId);
		} else {
			console.error("No post id found");
		}
	});
	
	$(document).on("click",".finDel",function(e){
		e.preventDefault();
		
		let postId = $(this).attr("data-post-id");
		
		if(typeof postId !== "undefined" && postId !== false){
			if(isDeleteModalOpen())
			closeDeleteModal();
			
			$.ajax({
				url: "/scripts/deletePost",
				data: {
					csrf_token: CSRF_TOKEN,
					post: postId
				},
				method: "POST",
				
				success: function(json){
					if(json.hasOwnProperty("status")){
						if(json.status == "done"){
							$('[data-status-render="' + postId + '"]').remove();
							$('[data-entry-id="' + postId + '"]').remove();
							$('[data-post-id="' + postId + '"]').remove();
						} else {
							console.error("Invalid status: " + json.status);
						}
					} else {
						console.log(json);
					}
				},
				
				error: function(xhr,status,error){
					console.log(xhr);
					console.log(status);
					console.log(error);
				}
			});
		} else {
			console.error("No post id found");
		}
	});
	
	$(document).on("click",".favoriteButton",function(e){
		e.preventDefault();
		
		let postId = $(this).attr("data-post-id");
		
		let favoritedHtml = '<i class="fas fa-star" style="color: gold"></i>';
		let unfavoritedHtml = '<i class="fas fa-star" style="color: gray"></i>';
		let loadingHtml = '<i class="fas fa-spinner fa-pulse"></i>';
		
		let pointer = $(this);
		
		if(typeof postId !== "undefined"){
			pointer.html(loadingHtml);
			
			$.ajax({
				url: "/scripts/toggleFavorite",
				data: {
					csrf_token: CSRF_TOKEN,
					post: postId
				},
				method: "POST",
				
				success: function(result){
					let json = result;
					
					if(json.hasOwnProperty("status")){
						let countDisplay = pointer.parent().find(".favoriteCount");
						let count = parseInt(countDisplay.html().trim());
						
						if(json.status == "Favorite added"){
							count++;
							countDisplay.html(count);
							
							pointer.html(favoritedHtml);
						} else {
							count--;
							countDisplay.html(count);
							
							pointer.html(unfavoritedHtml);
						}
					} else {
						console.log(result);
					}
				},
				
				error: function(xhr,status,error){
					console.log(xhr);
					console.log(status);
					console.log(error);
				}
			});
		}
	});
	
	$(document).on("click",".shareButton",function(e){
		e.preventDefault();
		
		let postId = $(this).attr("data-post-id");
		
		let sharedHtml = '<i class="fas fa-share-alt text-primary"></i>';
		let unsharedHtml = '<i class="fas fa-share-alt" style="color: gray"></i>';
		let loadingHtml = '<i class="fas fa-spinner fa-pulse"></i>';
		
		let pointer = $(this);
		
		if(typeof postId !== "undefined"){
			pointer.html(loadingHtml);
			
			$.ajax({
				url: "/scripts/toggleShare",
				data: {
					csrf_token: CSRF_TOKEN,
					post: postId
				},
				method: "POST",
				
				success: function(result){
					let json = result;
					
					if(json.hasOwnProperty("status")){
						let countDisplay = pointer.parent().find(".shareCount");
						let count = parseInt(countDisplay.html().trim());
						
						if(json.status == "Share added"){
							count++;
							countDisplay.html(count);
							
							pointer.html(sharedHtml);
						} else {
							count--;
							countDisplay.html(count);
							
							pointer.html(unsharedHtml);
						}
					} else {
						console.log(result);
					}
				},
				
				error: function(xhr,status,error){
					console.log(xhr);
					console.log(status);
					console.log(error);
				}
			});
		}
	});
	
	$(document).on("click",".ignoreParentClick",function(e){
		e.stopPropagation();
	});
	
	
	$(document).on("change keyup keydown paste",".postField",function(){
		let limit = POST_CHARACTER_LIMIT;
		let used = $(this).val().length;
		let left = limit-used;
		let counter = $(this).parent().find(".postCharacterCounter");
		
		if(left > 0){
			if(left > limit/2){
				if(left == 1){
					counter.html(left + " character left");
				} else {
					counter.html(left + " characters left");
				}
			} else {
				if(left == 1){
					counter.html("<span style=\"color: #F94F12;\">" + left + " character left</span>");
				} else {
					counter.html("<span style=\"color: #F94F12;\">" + left + " characters left</span>");
				}
			}
		} else if(left == 0){
			counter.html("<span style=\"color: #FF0000; font-weight: bold\">You have reached the character limit</span>");
		} else {
			left = left/(-1);
			
			if(left == 1){
				counter.html("<span style=\"color: #FF0000; font-weight: bold\">You are " + left + " character over the limit</span>");
			} else {
				counter.html("<span style=\"color: #FF0000; font-weight: bold\">You are " + left + " characters over the limit</span>");
			}
		}
	});
}

function toggleFollow(e,userID){
	if(typeof CRSF_TOKEN !== undefined){
		let token = CSRF_TOKEN;
		console.log(token);
		
		if(!~e.innerHTML.indexOf("fas fa-spinner")){
			console.log("Follow button clicked");
			
			e.innerHTML = '<i class="fas fa-spinner fa-pulse"></i>';
			
			$.ajax({
				url: "/scripts/toggleFollow",
				data: {
					csrf_token: token,
					user: userID
				},
				method: "POST",
				
				success: function(result){
					let json = result;
					
					if(json.hasOwnProperty("followStatus")){
						if(json.followStatus == 1){
							e.classList.add("unfollowButton");
							e.classList.add("btn-danger");
							
							e.classList.remove("pendingButton");
							e.classList.remove("btn-warning");
							
							e.classList.remove("followButton");
							e.classList.remove("btn-primary");
							
							e.innerHTML = "Unfollow";
						} else if(json.followStatus == 0) {
							e.classList.remove("unfollowButton");
							e.classList.remove("btn-danger");
							
							e.classList.remove("pendingButton");
							e.classList.remove("btn-warning");
							
							e.classList.add("followButton");
							e.classList.add("btn-primary");
							
							e.innerHTML = "Follow";
						} else if(json.followStatus == 2){
							e.classList.remove("unfollowButton");
							e.classList.remove("btn-danger");
							
							e.classList.add("pendingButton");
							e.classList.add("btn-warning");
							
							e.classList.remove("followButton");
							e.classList.remove("btn-primary");
							
							e.innerHTML = "Pending";
						}
					} else {
						console.log(result);
					}
				},
				
				error: function(xhr,status,error){
					console.log(xhr);
					console.log(status);
					console.log(error);
				}
			});
		}
	}
}

function setCookie(cname, cvalue, exdays) {
	var d = new Date();
	d.setTime(d.getTime() + (exdays*24*60*60*1000));
	var expires = "expires="+ d.toUTCString();
	document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

// https://stackoverflow.com/a/5968306
function getCookie(name) {
	var dc = document.cookie;
	var prefix = name + "=";
	var begin = dc.indexOf("; " + prefix);
	if (begin == -1) {
		begin = dc.indexOf(prefix);
		if (begin != 0) return null;
	}
	else
	{
		begin += 2;
		var end = document.cookie.indexOf(";", begin);
		if (end == -1) {
			end = dc.length;
		}
	}
	// because unescape has been deprecated, replaced with decodeURI
	//return unescape(dc.substring(begin + prefix.length, end));
	return decodeURI(dc.substring(begin + prefix.length, end));
} 

function hasCookie(name){
	return getCookie(name) != null;
}

function saveDismiss(id){
	setCookie("registeredAlert"+id,"closed",10);
}

function number_format (number, decimals, dec_point, thousands_sep) {
	// Strip all characters but numerical ones.
	number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
	var n = !isFinite(+number) ? 0 : +number,
	prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
	sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
	dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
	s = '',
	toFixedFix = function (n, prec) {
		var k = Math.pow(10, prec);
		return '' + Math.round(n * k) / k;
	};
	// Fix for IE parseFloat(0.55).toFixed(0) = 0;
	s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
	if (s[0].length > 3) {
		s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
	}
	if ((s[1] || '').length < prec) {
		s[1] = s[1] || '';
		s[1] += new Array(prec - s[1].length + 1).join('0');
	}
	return s.join(dec);
}