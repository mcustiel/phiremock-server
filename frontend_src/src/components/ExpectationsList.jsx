import React, { Component } from "react";
import Factory from "../Factory";
import JsonDisplay from "./JsonDisplay";

const factory = new Factory();

export default class ExpectationsList extends Component {
    
    constructor (props) {
        super(props);
        this.state = {requests: []};
        this.controller = factory.createExpectationsListController();
        this.getExpectations();
    }

    render() {
        this.getExpectations();
        return (
            <div id="requestsList">
                <JsonDisplay displayObject={this.state.requests} />
            </div>
        );
    }

    getExpectations() {
        this.controller.getExpectations().then(            
            (requests) => {
                console.log(requests);
                this.state.requests = requests;
            }
        );
    }

    render() {
        
        return (
            <div id="expectationsList">
                <JsonDisplay displayObject={this.state.requests} />
            </div>
        );
    }
}