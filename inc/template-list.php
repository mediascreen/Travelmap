<table id="travelmap-list">
	
	<tr><th></th><th>Destination</th><th>Arrival</th></tr>
	
	<?php $i = 1 ?>
	
	<?php foreach ( $places as $place ) { ?>
	
	<tr class="<?php echo $place['status'] ?>">
	
		<td><?php echo $i ?></td>
		
		<?php if ( ! empty( $place['url'] ) ) { ?>
			<td><a href="<?php echo $place['url'] ?>"><?php echo stripslashes( $place['city'] ) ?>, <?php echo stripslashes( $place['country'] ) ?></a></td>
		<?php } else { ?>
			<td><?php echo stripslashes( $place['city'] ) ?>, <?php echo stripslashes( $place['country'] ) ?></td>
		<?php } ?>
		
		<td><?php echo ! empty( $place['arrival'] ) ? date_i18n( $dateFormat, strtotime( stripslashes( $place['arrival'] ) ) ) : '-' ?></td>
	
	</tr>
	
	<?php $i++; ?>
	
	<?php } ?>
	
</table>