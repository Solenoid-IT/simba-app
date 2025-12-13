// © Solenoid-IT



import { Client } from '@/modules/Client.ts';
import { Response } from '@/modules/sRPC.ts';



export class Entity
{
    private client : Client;

    public name : string = '';

    public table : any;
    public form : any;
    public modal : any;

    public static requestToken : string = '';



    constructor (name : string, client : Client)
    {
        // (Getting the values)
        this.name   = name;
        this.client = client;
    }



    public async find (id : number) : Promise<Response>
    {
        // Returning the value
        return await this.client.run( `${ this.name }.find`, id );
    }

    public async list (input : any) : Promise<Response>
    {
        // Returning the value
        return await this.client.run( `${ this.name }.list`, input );
    }

    public async update (input : any) : Promise<Response>
    {
        // Returning the value
        return await this.client.run( `${ this.name }.update`, input, new Headers( { 'Request-Token': Entity.requestToken } ) );
    }

    public async insert (input : any) : Promise<Response>
    {
        // Returning the value
        return await this.client.run( `${ this.name }.insert`, input, new Headers( { 'Request-Token': Entity.requestToken } ) );
    }

    public async delete (ids : number[]) : Promise<Response|null>
    {
        if ( !confirm( 'Are you sure to delete the selected entries ?' ) ) return null;



        // (Getting the value)
        const response = await this.client.run( `${ this.name }.delete`, ids, new Headers( { 'Request-Token': Entity.requestToken } ) );

        if ( response.code === 200 )
        {// (Request OK)
            if ( this.table )
            {// Value found
                // (Deleting the records)
                this.table.removeRecordsByIds( ids );
            }
        }



        // Returning the value
        return response;
    }



    public async set (fn : string, input : any) : Promise<Response>
    {
        // Returning the value
        return await this.client.run( `${ this.name }.${ fn }`, input, new Headers( { 'Request-Token': Entity.requestToken } ) );
    }

    public async upsert (input : any, compact : boolean = false) : Promise<Response>
    {
        // (Getting the value)
        const fn = compact ? 'upsert' : ( input['id'] ? 'update' : 'insert' );



        // Returning the value
        return await this.client.run( `${ this.name }.${ fn }`, input, new Headers( { 'Request-Token': Entity.requestToken } ) );
    }



    public async export (input : any) : Promise<Response>
    {
        // Returning the value
        return await this.client.run( `${ this.name }.export`, input );
    }

    public async import (input : any) : Promise<Response>
    {
        // Returning the value
        return await this.client.run( `${ this.name }.import`, input, new Headers( { 'Request-Token': Entity.requestToken } ) );
    }



    setTable (table : any) : this
    {
        // (Getting the value)
        this.table = table;

        // Returning the value
        return this;
    }

    setForm (form : any) : this
    {
        // (Getting the value)
        this.form = form;

        // Returning the value
        return this;
    }

    setModal (modal : any) : this
    {
        // (Getting the value)
        this.modal = modal;

        // Returning the value
        return this;
    }



    displayNewRecordForm () : this
    {
        // (Resetting the form)
        this.form.reset();



        // (Showing the modal)
        this.modal.show();



        // Returning the value
        return this;
    }
}