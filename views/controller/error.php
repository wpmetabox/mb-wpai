<?php foreach ( $errors as $message ) : ?>
	<div class="error">
		<p>
			<?= esc_html( $message ) ?>
		</p>
	</div>
<?php endforeach; ?>