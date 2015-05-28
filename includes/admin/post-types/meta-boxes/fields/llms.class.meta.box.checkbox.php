<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* 
*/
class LLMS_Metabox_Checkbox_Field extends LLMS_Metabox_Field implements Meta_Box_Field_Interface
{
	/**
	 * Class constructor
	 * @param array $_field Array containing information about field
	 */
	function __construct($_field)
	{
		$this->field = $_field;
	}
	
	/**
	 * Outputs the Html for the given field
	 * @return HTML 
	 */
	public function Output()
	{
		global $post;
		
		parent::Output(); ?>
					
		<div class="llms-switch d-1of4 t-1of4 m-1of2">
		<div class="llms-toggle-icon-on">
			<?php echo LLMS_Svg::get_icon( 'llms-icon-checkmark', 'Off', 'Off', 'toggle-icon' ); ?>
		</div>
		<div class="llms-toggle-icon-off">
			<?php echo LLMS_Svg::get_icon( 'llms-icon-close', 'Off', 'Off', 'toggle-icon' ); ?>
			</div>

				<input 
					name="<?php echo esc_attr( $this->field['id'] ); ?>"
					id="<?php echo esc_attr( $this->field['id'] ); ?>"
					class="llms-toggle llms-toggle-round" 
					type="checkbox"
					value="<?php echo esc_attr( $this->field['value'] ); ?>"
					<?php echo $this->meta ? 'checked' : ''; ?>
				/> 

			<label for="<?php echo $this->field['id'] ?>"></label>
		</div>			
		<?php
		parent::CloseOutput();				
	}
}

