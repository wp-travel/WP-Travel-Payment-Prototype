import { addFilter } from '@wordpress/hooks'
import { useSelect, dispatch } from '@wordpress/data'
import { PanelRow, TextControl, ToggleControl } from '@wordpress/components'
import { __ } from '@wordpress/i18n'
import Select from 'react-select'

/**
 * Adding Payment Prototype Content
 */
addFilter( 'wp_travel_payment_gateway_fields_payment_prototype', 'wp-travel', (content) => {
    return [ ...content, <PaymentPrototypeContent /> ]
});

const PaymentPrototypeContent = () => {

    /**
     * Get Store Settings.
     */
    const { updateSettings } = dispatch('WPTravel/Admin');

    const allData = useSelect( (select) => {
        return select('WPTravel/Admin').getAllStore()
    }, [] );

    const {
        // wp_travel_express_checkout,
        // payment_option_express_checkout,
        // wp_travel_express_checkout_sand_box,
        payment_option_payment_prototype,
        wt_test_mode
    } = allData

    const updatePaymentPrototypeData = (storeName, storeKey, value) => { // storeName[storeKey] = value
        updateSettings({ ...allData, [storeName]: { ...allData[storeName], [storeKey]: value } })
    }

    return (
        <>
            {
                wt_test_mode === 'yes' ?
                <strong><p className="howTo" style={{color: '#ffffff', textAlign: 'center', backgroundColor: 'darkseagreen'}} >{ __( 'Test Mode Active', 'wp-travel-paypal-express-checkout' ) }</p></strong>
                : <strong><p className="howTo" style={{color: '#ffffff', textAlign: 'center', backgroundColor: 'darkseagreen'}} >{ __( 'Live Mode Active', 'wp-travel-paypal-express-checkout' ) }</p></strong>
            }
            {/** This is for admin settings for adding fields on WP Travel Settings / Payment tab. */}
            <h2>{ __( 'This is content from WP Travel Payment Prototype.', 'wp-travel' ) }</h2>

            <PanelRow>
                <label>{ __( 'Enable Payment Prototype', 'wp-travel-payment-prototype' ) }</label>
                <div className="wp-travel-field-value">
                    <ToggleControl
                        checked={ payment_option_payment_prototype == 'yes' }
                        onChange={ () => {
                            updateSettings({
                                ...allData,
                                payment_option_payment_prototype: 'yes' == payment_option_payment_prototype ? 'no': 'yes'
                            })
                        } }
                    />
                    <p className="description">{__( 'Check to enable Payment Prototype', 'wp-travel-payment-prototype' )}</p>
                </div>
            </PanelRow>
            {
                payment_option_payment_prototype === 'yes' && wt_test_mode === 'yes' &&
                <h3>{ __( 'Payment Enabled', 'wp-travel' ) }</h3>
                // <PanelRow>
                //     <label>{ __( 'Sandbox Client ID', 'wp-travel-paypal-express-checkout' ) }</label>
                //         <div className="wp-travel-field-value">
                //             <TextControl
                //                 value={ 'undefined' != typeof wp_travel_express_checkout_sand_box ? wp_travel_express_checkout_sand_box.client_id : ''}
                //                 onChange={
                //                     (value) => { updatePaypalExpressData('wp_travel_express_checkout_sand_box', 'client_id', value) }
                //                 }
                //             />
                //             <p className="description">{__('e.g. AQwT-8eC6_uJa0-m0Wg8YpC0vjNW_qU1eHhI...', 'wp-travel-paypal-express-checkout')}</p>
                //             <p className="description" dangerouslySetInnerHTML={{ __html: clientIdDesc }}></p>
                //         </div>
                // </PanelRow>
            }
        </>
    )
}