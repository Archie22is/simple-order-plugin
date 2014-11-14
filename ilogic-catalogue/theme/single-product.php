<?php /* Template name: Single Product Template */ ?>
<?php get_header(); ?>
<?php $post = get_page(get_the_ID()); ?>
<style>
.order_box{
	display:inline-block;
}
</style>
<div class="column_container">
	<div class="left_column">
		<h1 class="title">Products / cat name</h1>
		<div>
        <hr style="width:100%; display:inline-block;">
        <?php if (have_posts()) : ?>
		<?php while (have_posts()) : the_post(); ?>
        
            <div class="light_box_contaienr">
            	<?php echo get_the_post_thumbnail( get_the_ID(), 'medium'); ?>
            </div>
            
         <?php endwhile; ?>
         <?php endif; ?>
		</div>
        <hr style="width:100%; display:inline-block;" />
	</div>
    <div class="right_column">
    	<div class="right_heading">MESSAGE FORM</div>
        <form method="post" action="<?php echo get_bloginfo('siteurl'); ?>/message-submitted/" id="contact_form">
            <input type="text" class="validate[required,funcCall[checkInputs]] input" id="contact_name" name="contact_name" value="Name" onfocus="if(this.value == 'Name'){this.value = ''}" ><br />
            <input type="text" class="validate[required, custom[email],funcCall[checkInputs]] input"  id="contact_email" name="contact_email" value="Email Address" onfocus="if(this.value == 'Email Address'){this.value = ''}"><br />
            <textarea class="validate[required,funcCall[checkInputs]] contact_textarea" name="contact_textarea" id="contact_textarea" onfocus="if(this.value == 'Message'){this.value = ''}">Message</textarea>
            <input type="submit" id="submit_contact" class="submit" value="SUBMIT">
        </form>
        
        <div class="right_heading">CATEGORIES</div>
        <ul class="side_categories">
        <?php
		
	
		 $categories = get_product_categories();
		 
		 foreach($categories as $category){
			 $cur_class = '';
			 if(isset($_GET['products_category'])){
				if($category["name"] ==  $_GET['products_category']){
					$cur_class = ' class="cur_cat" ';
				}
			 }
			 echo '<li><a '.$cur_class.' href="'.get_bloginfo('siteurl').'/products/?products_category='.$category["name"] .'">'.$category["name"] .'</a></li>';
			 
			 if(isset($category['children'])){
				 foreach($category['children'] as $child){
					 $cur_child_class = '';
					 if(isset($_GET['products_sub_category'])){
						if($child["name"] ==  $_GET['products_sub_category']){
							$cur_child_class = ' class="cur_sub_cat" ';
						}
					 }
					echo '<li class="child_category"><a '.$cur_child_class.' href="'.get_bloginfo('siteurl').'/products/?products_category='.$category["name"] .'&products_sub_category='.$child["name"] .'">'.$child["name"] .'</a></li>';
				 }
			 }
		 }
		 
		  ?> 
        </ul>
    </div>
</div>
<?php get_footer(); ?>
