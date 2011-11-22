<div class="wrap">
	<div class="icon32" id="icon-options-general"></div>
	<h2>Travelmap destinations</h2>
	<p>Add locations to show them on your map. Only <em>city</em> and <em>country</em> are obligatory. To automatically show where you are right now you need to fill in <em>arrival</em> date.</p>
	<p>If you supply an <em>URL</em>, the city and country in the list will be linked. It needs to be a full URL (start with http://). Use it to link to, for example, a travel report, Wikipedia article or photo album.</p>
	<p>Leave <em>latitude</em> and <em>longitude</em> empty to geocode the location automatically when you save. You should only input your own values if there is something wrong with the geocoding.</p>
	<p>To show your map or list you insert shortcodes in your post or page.<br />
	 For the map (height of map in pixels):<br />
	<code>[travelmap-map height=400]</code><br />
	For the list:<br />
	<code>[travelmap-list]</code></p>
	<p><a href="http://travelingswede.com/travelmap/">Plugin homepage</a></p>

	<table id="travelmap-admin-table" class="widefat" cellspacing="0">
		<thead class="<?php echo wp_create_nonce( 'travelmap' );?>">
			<tr>
				<th scope="col" class="handle manage-column"></th>
				<th scope="col" class="count manage-column"></th>
				<th scope="col" class="city manage-column">City</th>
				<th scope="col" class="country manage-column">Country</th>
				<th scope="col" class="url manage-column">URL</th>
				<th scope="col" class="arrival manage-column">Arrival</th>
				<th scope="col" class="lat manage-column">Latitude</th>
				<th scope="col" class="lng manage-column">Longitude</th>
				<th scope="col" class="buttons1 manage-column"></th>
				<th scope="col" class="buttons2 manage-column"></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $places as $place ) { ?>
				<?php $i++; ?>
					
					<tr class="<?php echo $place['status'] ?>">
						<td class="handle"><span class="image"></span></td>
						<td class="count"> <?php echo $i ?></td>
						<td class="city"><?php echo stripslashes($place['city']) ?></td>
						<td class="country"><?php echo stripslashes($place['country']) ?></td>
						<td class="url"><?php echo $place['url'] ?></td>
						<td class="arrival"><?php echo stripslashes($place['arrival']) ?></td>
						<td class="lat"><?php echo $place['lat'] ?></td>
						<td class="lng"><?php echo $place['lng'] ?></td>
						<td class="buttons1"><a href="#" class="button-secondary edit" title="Edit row">Edit</a></td>
						<td class="buttons2"><a href="#" class="delete" title="Delete row">Delete</a></td>
					</tr>
				<?php } ?>
		</tbody>
	</table>
	
	<a id="add-location" class="button-secondary" href="#" title="Add row for new location">Add location</a>
</div>