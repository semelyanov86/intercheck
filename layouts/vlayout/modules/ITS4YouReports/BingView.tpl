{if '' !== $API_KEY}
    <script type='text/javascript' src='http://www.bing.com/api/maps/mapcontrol?callback=GetMap&key={$API_KEY}}' async defer></script>
    <script type='text/javascript'>
        var map, infobox;
        {assign var=CENTERED_MAP value='0'}

        function GetMap() {
            console.log('map zoom: {$MAP_ZOOM}');
            console.log('map zoom: {$MAP_ZOOM}');
            map = new Microsoft.Maps.Map('#R4YouMap', {
                zoom: {$MAP_ZOOM},
                credentials: '{$API_KEY}'
            });

            //Create an infobox at the center of the map but don't show it.
            infobox = new Microsoft.Maps.Infobox(map.getCenter(), {
                visible: false
            });

            //Assign the infobox to a map instance.
            infobox.setMap(map);

            //Create random locations in the map bounds.
            {foreach item=PIN_DATA key=PIN_I from=$FOUND_DATA}
            var lat = parseFloat({$PIN_DATA['0']});
            var long = parseFloat({$PIN_DATA['1']});
            var loc = new Microsoft.Maps.Location(lat, long);

            /** Center view */
            {if '0' === $CENTERED_MAP}
                {assign var=CENTER_MAP value="map.setView({ center: new Microsoft.Maps.Location(lat, long)});"}
                {$CENTER_MAP}
                {assign var=CENTERED_MAP value='1'}
            {/if}

            var pin = new Microsoft.Maps.Pushpin(loc);
            if (0 < '{$PIN_DATA['3']|count_characters}' && 0 < '{$PIN_DATA['4']|count_characters}') {
                pin.metadata = {
                    title: `{$PIN_DATA['3']}`,
                    description: `{$PIN_DATA['4']}<br><i>{$PIN_DATA['2']}</i>`
                };
            } else if (0 < '{$PIN_DATA['3']|count_characters}') {
                pin.metadata = {
                    title: `{$PIN_DATA['3']}`,
                    description: `<i>{$PIN_DATA['2']}</i>`
                };
            } else if (0 < '{$PIN_DATA['2']|count_characters}') {
                pin.metadata = {
                    title: ``,
                    description: `<i>{$PIN_DATA['2']}</i>`
                };
            }
            //Add a click event handler to the pushpin.
            Microsoft.Maps.Events.addHandler(pin, 'click', pushpinClicked);
            //Add pushpin to the map.
            map.entities.push(pin);
            {/foreach}
        }

        function pushpinClicked(e) {
            //Make sure the infobox has metadata to display.
            if (e.target.metadata) {
                //Set the infobox options with the metadata of the pushpin.
                infobox.setOptions({
                    location: e.target.getLocation(),
                    title: e.target.metadata.title,
                    description: e.target.metadata.description,
                    visible: true
                });
            }
        }
    </script>
    {if '1' eq $DEV_MODE}
        <div id="R4YouMap" style='position: absolute; bottom: 0; right: 0; width: 50%;height: 50%;'></div>
    {else}
        <div id="R4YouMap" style='position: absolute; bottom: 0; right: 0; width: 100%;height: 100%;'></div>
    {/if}
{else}
    API Key can not be empty!
{/if}