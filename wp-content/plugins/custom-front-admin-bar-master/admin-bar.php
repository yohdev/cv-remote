<?php 
	
		
	if ( current_user_can( "administrator" ) && ( ! is_admin_bar_showing() ) || current_user_can( "editor" ) && ( ! is_admin_bar_showing() )  )  { ?>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Lato&display=swap" rel="stylesheet">
	<link href="<?php bloginfo(
   "wpurl"
 ); ?>/wp-includes/css/dashicons.min.css" rel="stylesheet">

	<div class="top-edit-menu">
		<a data-title="Edit Page" class="edit-btn" href="<?php bloginfo(
    "wpurl"
  ); ?>/wp-admin/post.php?post=<?php the_ID(); ?>&action=edit"><span class="dashicons dashicons-welcome-write-blog"></span></a>
		<a data-title="New Page" class="edit-btn" href="<?php bloginfo(
    "wpurl"
  ); ?>/wp-admin/post-new.php?post_type=page"><span class="dashicons dashicons-welcome-add-page"></span></a>
  <a data-title="New Story" class="edit-btn" href="<?php bloginfo(
	  "wpurl"
	); ?>/wp-admin/post-new.php?post_type=stories"><span class="dashicons dashicons-welcome-add-page"></span></a>
		<a data-title="Pages" class="edit-btn" href="<?php bloginfo(
    "wpurl"
  ); ?>/wp-admin/edit.php?post_type=page"><span class="dashicons dashicons-admin-page"></span></a>
		<a data-title="Reusable Blocks" class="edit-btn" href="<?php bloginfo(
    "wpurl"
  ); ?>/wp-admin/edit.php?post_type=wp_block"><span class="dashicons dashicons-controls-repeat"></span></a>
		<a data-title="Dashboard" class="edit-btn" href="<?php bloginfo(
    "wpurl"
  ); ?>/wp-admin/"><span class="dashicons dashicons-dashboard"></span></a>
		<a data-title="Apperance Editor" class="edit-btn" href="<?php bloginfo(
     "wpurl"
   ); ?>/wp-admin/site-editor.php"><span class="dashicons dashicons-admin-appearance"></span></a>
	
	</div>


	<style type="text/css">
	.top-edit-menu {
		font-family: "Lato", sans-serif;
		display: flex;
		align-items: center;
		justify-content: center;
		position: fixed;
		top: -44px;
		width: calc(100% - 40px);
		left: 20px;
		text-align: center;
		z-index: 99999;
		height: auto;
		border-style: solid;
		border-width: 0 0 15px 0;
		border-color: transparent;
		padding-bottom: 0 0 18px 0;
		border-radius: 0 0 6px 6px;
		transition: all 0.5s;
	}
	.top-edit-menu:hover {
		top: 0;
		border-width: 0 0 10px 0;
	}
	.top-edit-menu .edit-btn:first-child {
		border-radius: 0 0 0 6px;
	}
	.top-edit-menu .edit-btn:last-child {
		border-radius: 0 0 6px 0;
	}
	.edit-btn {
		color: #fff !important;
		text-decoration: none !important;
		display: flex;
		justify-content: center !important;
		align-items: center !important;
		top: 0;
		left: 0;
		padding: 0 !important;
		background-color: #000;
		transition: all 0.5s;
		-webkit-box-shadow: 0 0 5px #000;
		border-style: solid;
		border-width: 0 1px 1px 1px;
		border-color: #999;
		height: 42px !important;
		opacity: 0.8;
		transition: all 0.5s;
		cursor: pointer;
		line-height: 1em;
		width: 42px !important;
	}
	.edit-btn:hover {
		opacity: 1;
		top: 0;
		background-color: #333;
	}
	.edit-btn.add {
		-webkit-border-radius: 0;
		border-radius: 0;
		border-style: solid;
		border-color: #fff;
		text-decoration: none;
		color: #fff;
	}

	.edit-btn .dashicons {
		display: inline-block !important;
		font-size: 24px;
		line-height: 0.75em !important;
		margin: 0 !important;
		padding: 0 !important;
	}
	[data-title]:hover:after {
		opacity: 1;
		transition: all 0.3s ease 0.3s;
		visibility: visible;
	}
	[data-title]:after {
		content: attr(data-title);
		display: flex;
		justify-content: center;
		align-items: center;
		background-color: #333;
		border-radius: 6px;
		color: #fff;
		font-size: 14px;
		position: absolute;
		padding: 0 10px 0 10px;
		bottom: -35px;
		height: 30px;
		line-height: 1em;
		left: 0;
		white-space: nowrap;
		box-shadow: 0 0 1px rgba(0, 0, 0, 0.79);
		opacity: 0;
		z-index: 99999;
		visibility: hidden;
		border-style: solid;
		border-width: 1px;
		border-color: #999;
	}
	[data-title] {
		position: relative;
	}

	</style>

<?php } ?>
