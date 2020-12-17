import React, {Component} from 'react';

export default class NavLink extends Component {

    render() {
        return (
            <li key={this.props.name}>
                <a href={'#' + this.props.name}
                id={this.props.id}
                className={this.props.className}
                onClick={this.props.onClick}
                >{this.props.text}</a>
            </li>
        );
    }

}