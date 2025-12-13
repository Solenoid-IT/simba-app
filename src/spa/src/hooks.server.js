import { building, dev } from '$app/environment';



/** @type {import('@sveltejs/kit').Handle} */
export const handle = async function ({ event, resolve })
{
    // (Getting the value)
    const svelteVars =
    {
        //'app_host': process.env.APP_HOST
    }
    ;



    // (Getting the value)
    const response = await resolve
    (
        event,
        {
            'transformPageChunk': function ({ html })
            {
                // Returning the value
                return html;



                if ( building ) return html;



                // (Setting the value)
                let content = html;

                for ( const key in svelteVars )
                {// Processing each entry
                    // (Getting the value)
                    content = content.replace( new RegExp( `%${key}%`, 'g' ), svelteVars[ key ] );
                }



                // Returning the value
                return content;
            }
        }
    )
    ;



    // Returning the value
    return response;
};