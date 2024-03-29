<?php
/**
 * @version   1.0 March 5, 2014
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2014 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class GantryDropdownMenuLayout extends AbstractRokMenuLayout {

	static $jsLoaded = false;

	public function stageHeader() {
		global $gantry;

		if ( !self::$jsLoaded && $gantry->get( 'layout-mode', 'responsive' ) == 'responsive' ) {
			if ( !( $gantry->browser->name == 'ie' && $gantry->browser->shortver < 9 ) ) {
				$gantry->addScript( $gantry->gantryUrl . '/widgets/gantrymenu/themes/default/js/rokmediaqueries.js' );
				if ( $gantry->get( 'responsive-menu' ) == 'selectbox' ) {
					$gantry->addScript( $gantry->gantryUrl . '/widgets/gantrymenu/themes/default/js/responsive.js' );
					$gantry->addScript( $gantry->gantryUrl . '/widgets/gantrymenu/themes/default/js/responsive-selectbox.js' );
				} else if( file_exists( $gantry->gantryPath . '/widgets/gantrymenu/themes/default/js/sidemenu.js' ) && ( $gantry->get( 'responsive-menu' ) == 'panel' ) ) {
					$gantry->addScript( $gantry->gantryUrl . '/widgets/gantrymenu/themes/default/js/sidemenu.js' );
				}
			}
			self::$jsLoaded = true;
		}
		
		$gantry->addLess( 'menu.less' );
	}

	protected function renderItem( RokMenuNode &$item, &$menu ) {
		global $gantry;
		
		$wrapper_css = '';
		$ul_css = '';
		$group_css = '';

		//get columns count for children
		$columns = $item->getAttribute( 'columns' );
		//get custom image
		$custom_image = $item->getAttribute( 'customimage' );
		//get the custom icon
		$custom_icon = $item->getAttribute( 'customicon' );

		//add default link class
		$item->addLinkClass( 'item' );
		
		if ( $custom_image && $custom_image != -1 ) $item->addLinkClass( 'image' );
		if ( $custom_icon && $custom_icon != -1 ) $item->addLinkClass( 'icon' );
			
		$dropdown_width = intval( trim( $item->getAttribute( 'dropdown_width' ) ) );
		$column_widths = explode( ",", $item->getAttribute( 'column_widths' ) );

		if ( trim( $columns ) == '' ) $columns = 1;
		if ( trim( $dropdown_width ) == 0 ) $dropdown_width = 180;

		$wrapper_css = ' style="width:' . trim( $dropdown_width ) . 'px;"';

		$col_total = 0; $cols_left = $columns;
		if ( trim( $column_widths[0] != '' ) ) {
			for ( $i=0; $i < $columns; $i++ ) {
				if( isset( $column_widths[$i] ) ) {
					$ul_css[] = ' style="width:' . trim( intval( $column_widths[$i] ) ) . 'px;"';
					$col_total += intval( $column_widths[$i] );
					$cols_left--;
				} else {
					$col_width = floor( intval( ( intval( $dropdown_width ) - $col_total ) / $cols_left ) );
					$ul_css[] = ' style="width:' . $col_width . 'px;"';
				}
			}
		} else {
			for ( $i=0; $i < $columns; $i++ ) {
				$col_width = floor( intval( $dropdown_width) / $columns );
				$ul_css[] = ' style="width:' . $col_width . 'px;"';
			}
		}
		
		// Menu Item Grouping
		
		$grouping = $item->getAttribute( 'children_group' );
		if ( $grouping == 1 ) $item->addListItemClass( 'grouped' );    
		$child_type = 'menuitems';
		
		$distribution = $item->getAttribute( 'distribution' );
		$manual_distribution = explode( ",", $item->getAttribute( 'manual_distribution' ) );

		//not so elegant solution to add subtext
		$item_subtext = $item->getAttribute( 'item_subtext' );
		if ( $item_subtext == '' ) $item_subtext = false;
		else $item->addLinkClass( 'subtext' );

		if ( $item->getLink() == '#' ) {
			$item->setLink( 'javascript:void(0);' );
		}
				  
		?>
		
		<li <?php if( $item->hasListItemClasses() ) : ?>class="<?php echo $item->getListItemClasses(); ?>"<?php endif;?> <?php if( $item->getCssId() ) : ?>id="<?php echo $item->getCssId(); ?>"<?php endif;?>>

			<a <?php if( $item->hasLinkClasses() ) : ?>class="<?php echo $item->getLinkClasses(); ?>"<?php endif;?> <?php if( $item->hasLink() ) : ?>href="<?php echo $item->getLink(); ?>"<?php endif;?> <?php if( $item->getTarget() ) : ?>target="<?php echo $item->getTarget(); ?>"<?php endif;?> <?php if( $item->hasLinkAttribs() ) : ?><?php echo $item->getLinkAttribs(); ?><?php endif;?>>

				<?php if( $custom_image && $custom_image != -1 ) : ?>
					<img class="menu-image" src="<?php echo $gantry->templateUrl . '/images/icons/' . $custom_image; ?>" alt="<?php echo $custom_image; ?>" />
				<?php endif; ?>
				
				<?php
				if( $custom_icon && $custom_icon != -1 ) {
					echo '<i class="' . $custom_icon . '">' . $item->getTitle() . '</i>';
				} else {
					echo $item->getTitle();
				}
				if( !empty( $item_subtext ) ) {
					echo '<em>'. $item_subtext . '</em>';
				}
				?>				
			</a>

			<?php if( $item->hasChildren() ): ?>
				<span class="dropdown-spacer"></span>
				<?php if ( $grouping == 0 or $item->getLevel() == 0 ) :

					if ( $distribution == 'inorder' ) {
						$count = sizeof( $item->getChildren() );
						$items_per_col = intval( ceil( $count / $columns ) );
						$children_cols = array_chunk( $item->getChildren(), $items_per_col );
					} elseif ( $distribution == 'manual' ) {
						$children_cols = $this->array_fill( $item->getChildren(), $columns, $manual_distribution );
					} else {
						$children_cols = $this->array_chunkd( $item->getChildren(), $columns );
					}
					$col_counter = 0;
					?>
					<div class="dropdown <?php if( $item->getLevel() > 0 ) echo 'flyout '; ?><?php echo 'columns-' . $columns . ' '; ?>"<?php echo $wrapper_css; ?>>
						<?php foreach( $children_cols as $col ) : ?>
						<div class="column col<?php echo intval($col_counter)+1; ?>" <?php echo $ul_css[$col_counter++]; ?>>
							<ul class="l<?php echo $item->getLevel() + 2; ?>">
								<?php foreach ( $col as $child ) : ?>
									<?php echo $this->renderItem( $child, $menu ); ?>
								<?php endforeach; ?>
							</ul>
						</div>
						<?php endforeach;?>
					</div>

				<?php else : ?>

					<ol class="<?php echo $group_css; ?>">
						<?php foreach ( $item->getChildren() as $child ) : ?>
							<?php echo $this->renderItem( $child, $menu ); ?>
						<?php endforeach; ?>
					</ol>

				<?php endif; ?>
			<?php endif; ?>
		</li>
		<?php
	}
	
	function array_fill( array $array, $columns, $manual_distro ) {
	
		$new_array = array();

		array_unshift( $array, null );
		
		for ( $i=0; $i<$columns; $i++ ) {
			if ( isset( $manual_distro[$i] ) ) {
				$manual_count = $manual_distro[$i];
				for ( $c=0; $c<$manual_count; $c++ ) {
					//echo "i:c " . $i . ":". $c;
					$element = next( $array );
					if ( $element ) $new_array[$i][$c] = $element;
				}
			}
		}
		
		return $new_array;
	
	}
	
	function array_chunkd( array $array, $chunk ) {
		if ( $chunk === 0 )
			return $array;

		// number of elements in an array
		$size = count( $array );

		// average chunk size
		$chunk_size = $size / $chunk;

		// calculate how many not-even elements eg in array [3,2,2] that would be element "3"
		$real_chunk_size = floor( $chunk_size );
		$diff = $chunk_size - $real_chunk_size;
		$not_even = $diff > 0 ? round( $chunk * $diff ) : 0;

		// initialise values for return
		$result = array();
		$current_chunk = 0;

		foreach ( $array as $key => $element )
		{
			$count = isset( $result[$current_chunk] ) ? count( $result[$current_chunk] ) : 0;

			// move to a new chunk?
			if ( $count == $real_chunk_size && $current_chunk >= $not_even || $count > $real_chunk_size && $current_chunk < $not_even )
				$current_chunk++;

			// save value
			$result[$current_chunk][$key] = $element;
		}

		return $result;
	}
	
	public function calculate_sizes ( array $array ) {
		return implode( ', ', array_map( 'count', $array ) );
	}
	
	public function curPageURL( $link ) {
		$pageURL = 'http';
		if ( isset( $_SERVER["HTTPS"] ) && $_SERVER["HTTPS"] == "on" ) {$pageURL .= "s";}
		$pageURL .= "://";
		if ( $_SERVER["SERVER_PORT"] != "80" ) {
			$pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
		} else {
			$pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
		}
	
		$replace = str_replace( '&', '&amp;', ( preg_match( "/^http/", $link ) ? $pageURL : $_SERVER["REQUEST_URI"] ) );

		return $replace == $link;
	}
	
	public function renderMenu( &$menu ) {
		global $gantry;	
		ob_start(); ?>
		<div class="gf-menu-device-container responsive-type-<?php echo $gantry->get( 'responsive-menu' ); ?>"></div>

		<ul class="gf-menu l1 ">
			<?php foreach ( $menu->getChildren() as $item ) : ?>
				<?php echo $this->renderItem( $item, $menu ); ?>
			<?php endforeach; ?>
		</ul>
		
		<?php return ob_get_clean();
	}
	
}