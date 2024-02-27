import { Panel, PanelBody } from "@wordpress/components"
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { TextControl } from '@wordpress/components';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { Spinner } from '@wordpress/components';
import { __experimentalText as Text } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

import AsyncSelect from 'react-select/async';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @return {WPElement} Element to render.
 */
export default function Edit( { attributes, setAttributes } ) {
	const blockProps = useBlockProps();

  const [loadedResults, setLoadedResults] = useState([]);

	const updateObjectID = (newID) => {
		setAttributes({ objectId: Number(newID) });
	};

  const updateObjectTitle = ( newTitle ) => {
		setAttributes( { objectTitle: newTitle } );
	};

  const handleInputChange = (newValue) => {
    const selectedOption = loadedResults.find(
      (option) => Number(option.sourceId.value) === Number(newValue.value)
    );

    updateObjectID(newValue.value);
    updateObjectTitle(selectedOption?.title?.value);
  };

  const getSearchResults = async (inputValue) => {
    const response = await fetch(
      inputValue
        ? `/wp-json/mocp/v1/emuseum-proxy/?path=search/${inputValue}/objects/json`
        : `/wp-json/mocp/v1/emuseum-proxy/?path=objects/json`
    );

    const responseBody = await response.json();

    const results = Boolean(inputValue)
      ? responseBody?.results
      : responseBody?.objects;

    const formattedResults = results.map( ( result ) => ( {
			label: `${result?.title?.value} (${result?.sourceId?.value})`,
			value: parseInt(result?.sourceId?.value, 10),
		} ) );

    setLoadedResults(results);

    return formattedResults;
  };

	return (
    <>
      <InspectorControls>
        <Panel>
          <PanelBody>
            <Text>
              Search for an object:
            </Text>

            <AsyncSelect
              cacheOptions
              defaultOptions
              loadOptions={getSearchResults}
              onChange={handleInputChange}
              value={attributes?.objectId ? {
                label: `${attributes?.objectTitle} (${attributes.objectId})`,
                value: attributes.objectId,
              } : null}
              // Fix for the dropdown menu z-index:
              menuPortalTarget={document.body}
              menuPosition={'fixed'}
              styles={{
                menuPortal: (provided) => ({ ...provided, zIndex: 9999 }),
                menu: (provided) => ({ ...provided, zIndex: 9999 })
              }}
            />

            <TextControl
              label="Or if you have an eMuseum ID, set it here:"
              value={ attributes?.objectId || '' }
              onChange={ updateObjectID }
            />
          </PanelBody>
        </Panel>
      </InspectorControls>

      <div {...blockProps}>
        <ServerSideRender
          block="mocp/emuseum-object"
          attributes={attributes}
          LoadingResponsePlaceholder={Spinner}
        />
      </div>
    </>
	);
}
