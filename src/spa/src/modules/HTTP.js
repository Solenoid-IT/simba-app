// © Solenoid-IT

export class Response
{
    constructor (fetchResponse)
    {
        // (Getting the value)
        this._response = fetchResponse;



        // (Getting the values)
        this.code    = this._response.status;
        this.headers = this._response.headers;
        this.body    = this._response.body;
    }



    // Returns [string|false]
    fetchHeader (name)
    {
        // Returning the value
        return this.headers.get( name ) ?? false;
    }

    // Returns [Promise:mixed]
    async fetchBody (type)
    {
        // (Getting the body)
        let value = this.body;

        switch (type)
        {
            case 'json':
                // (Getting the value)
                value = await this._response.json();
            break;

            case 'text':
                // (Getting the value)
                value = await this._response.text();
            break;

            case 'blob':
                // (Getting the value)
                value = await this._response.blob();
            break;

            case 'arrayBuffer':
                // (Getting the value)
                value = await this._response.arrayBuffer();
            break;

            default:
                // Throwing an eerror
                throw new Error( `Type '${ type } is not recognized'` );
        }



        // Returning the value
        return value;
    }
}

export class Request
{
    constructor (method = 'GET', headers = {}, body = null)
    {
        // (Getting the values)
        this.method  = method;
        this.headers = headers;
        this.body    = body;
    }

    // Returns [Promise:Response]
    async send (url, timeout = 0)
    {
        // (Getting the value)
        this._controller = new AbortController();

        if ( timeout > 0 )
        {// Match OK
            // (Setting the timeout)
            const timeoutId = setTimeout
            (
                function ()
                {
                    // (Aborting the request)
                    this.abort();
                },
                timeout * 1000
            )
            ;
        }



        // (Sending the request)
        const response = await fetch
        (
            url,
            {
                'method':      this.method,
                'headers':     this.headers,
                'body':        this.body,

                'credentials': 'include',

                'signal':      this._controller.signal,

                /*
                'mode': 'cors',
                'redirect': 'follow',
                */
            }
        )
        ;



        // Returning the value
        return new Response( response );
    }

    // Returns [void]
    abort ()
    {
        // (Aborting the request)
        this._controller.abort();
    }
}

export class RequestQueue
{
    constructor ()
    {
        // (Getting the value)
        this._requests = [];
    }



    // Returns [void]
    enqueue (request)
    {
        // (Appending the value)
        this._requests.push( request );
    }

    // Returns [Request]
    dequeue ()
    {
        // Returning the value
        return this._requests.shift();
    }
}