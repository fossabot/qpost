import React, {Component} from "react";
import User from "../Entity/Account/User";
import FeedEntry from "../Entity/Feed/FeedEntry";
import API from "../API/API";
import {Alert, Spinner} from "reactstrap";
import NightMode from "../NightMode/NightMode";
import BaseObject from "../Serialization/BaseObject";
import FeedEntryListItem from "./FeedEntryListItem";

export default class FeedEntryList extends Component<{
	user?: User
}, {
	entries: FeedEntry[] | null,
	error: string | null
}> {
	constructor(props) {
		super(props);

		this.state = {
			entries: null,
			error: null
		}
	}

	componentDidMount(): void {
		const parameters = {};

		API.handleRequest("/feed", "GET", parameters, data => {
			let entries: FeedEntry[] = [];

			data.results.forEach(result => entries.push(BaseObject.convertObject(FeedEntry, result)));

			this.setState({entries});
		}, error => {
			console.log(error);
			this.setState({error});
		});
	}

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		if (this.state.entries !== null) {
			if (this.state.entries.length > 0) {
				return <ul className={"list-group feedContainer mt-2"}>
					{this.state.entries.map((entry, i) => {
						return <FeedEntryListItem key={i} entry={entry}/>
					})}
				</ul>;
			} else {
				return <Alert color={"info"}>There is nothing here.</Alert>;
			}
		} else if (this.state.error !== null) {
			return <Alert color={"danger"}>{this.state.error}</Alert>;
		} else {
			return <div className={"text-center my-3"}>
				<Spinner type={"grow"} color={NightMode.spinnerColor()} size={"lg"}/>
			</div>
		}
	}
}