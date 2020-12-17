import React, { Component } from "react";
import Factory from "../Factory";
import JsonDisplay from "./JsonDisplay";

const factory = new Factory();

export default class RequestsList extends Component {

    constructor (props) {
        super(props);
        this.state = {requests: []};
        this.controller = factory.createRequestListController();
        this.getRequests();
    }

    render() {
        this.getRequests();
        return (
            <div id="requestsList">
                <JsonDisplay displayObject={this.state.requests} />
            </div>
        );
    }

    getRequests() {
        this.controller.getRequests().then(            
            (requests) => {
                console.log(requests);
                this.state.requests = requests;
            }
        );
    }
}