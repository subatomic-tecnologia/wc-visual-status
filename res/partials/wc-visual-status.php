<div class="wc-visual-status" style="--progress: <?php echo esc_attr( $status[ 'progress' ] ) ?>">
<?php foreach ( $statusGroups as $group ) { ?>
  <?php
  $classes = array();

  // Generic handling of...

  // Current class
  if ( $group[ 'id' ] == $status[ 'id' ] )
    $classes[] = 'current';

  // Groups at end of the bar
  if ( 1.00 <= $group[ 'progress' ] )
    $classes[] = 'last-step';

  // Has this already been completed?
  if ( $status[ 'progress' ] >= $group[ 'progress' ] )
    $classes[] = 'active';
  else
    $classes[] = 'pending';

  // Handles cases when the bar is full
  if ( 1.00 > $status[ 'progress' ] ) {

    // Fixed class when not complete
    if ( isset( $group[ 'fixed' ] ) && $group[ 'fixed' ] )
      $classes[] = 'fixed';

  }
  ?>
  <div
    class="wc-visual-status-item <?php echo implode( ' ', $classes ) ?>"
    style="--status-progress: <?php echo esc_attr( $group[ 'progress' ] ) ?>"
  >
    <img
      class="wc-visual-status-item-icon"
      src="<?php echo esc_attr( $group[ 'icon' ] ) ?>"
      alt="<?php echo esc_attr( $group[ 'title' ] ) ?>"
    />
    <span
      class="wc-visual-status-item-text"
    >
      <?php echo htmlentities( $group[ 'title' ] ) ?>
    </span>
  </div>
<?php } ?>
</div>